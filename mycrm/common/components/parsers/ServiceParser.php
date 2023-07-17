<?php

namespace common\components\parsers;

use core\models\company\Company;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\InsuranceCompany;
use core\models\Service;
use core\models\ServiceCategory;
use core\repositories\exceptions\NotFoundException;
use core\services\TransactionManager;
use Exception;
use PHPExcel_Worksheet;
use Yii;

class ServiceParser extends BaseParser
{
    private $transaction_manager;

    public function __construct(TransactionManager $transaction_manager, array $config = [])
    {
        parent::__construct($config);
        $this->transaction_manager = $transaction_manager;
    }

    /**
     * @param PHPExcel_Worksheet $sheet
     * @param Company $company
     *
     * @throws Exception
     */
    public function parse(PHPExcel_Worksheet $sheet, Company $company)
    {
        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {

            $dataRow = $sheet->rangeToArray(
                'A' . $row . ':J' . $row,
                null,
                true,
                false
            );

            $progress = number_format($row / $highestRow * 100);
            Yii::$app->session->set('progress', $progress);

            if (empty($dataRow[0][0])) {
                continue;
            }

            try {
                $this->transaction_manager->execute(function () use (
                    $sheet,
                    $company,
                    $dataRow
                ) {

                    $data = self::getData($dataRow, $company);
                    $divisionService = $this->getDivisionService($data);

                    if (!$divisionService->save()) {
                        throw new \DomainException(array_values($divisionService->firstErrors)[0]);
                    }

                    $divisionService->unlinkAll('divisions', true);
                    if (!empty($data['division_id'])) {
                        $division = Division::findOne($data['division_id']);
                        if ($division) {
                            $divisionService->link('divisions', $division);
                        }
                    }

                    $divisionService->unlinkAll('categories', true);
                    if (!empty($data['category_ids']) && is_array($data['category_ids'])) {
                        foreach ($data['category_ids'] as $category_id) {
                            $category = ServiceCategory::findOne($category_id);
                            if ($category) {
                                $divisionService->link('categories', $category);
                            }
                        }
                    }

                    $this->_savedCounter++;
                });
            } catch (Exception $e) {
                $this->_incorrectModels[] = [
                    'row'   => $row,
                    'error' => $e->getMessage(),
                ];
            }
        }
    }

    /**
     * @param array $attributes
     *
     * @return DivisionService
     */
    private function getDivisionService(array $attributes)
    {
        /* @var DivisionService $divisionService */
        $divisionService = DivisionService::find()
            ->joinWith('divisions', false)
            ->andWhere([
                'service_name'         => $attributes['service_name'],
                '{{%divisions}}.id'    => $attributes['division_id'],
                'insurance_company_id' => $attributes['insurance_company_id'],
            ])
            ->one();

        if ($divisionService === null) {
            $divisionService = new DivisionService();
            $divisionService->setAttributes($attributes);
        } else {
            $divisionService->edit(
                $attributes['average_time'],
                $attributes['publish'],
                $attributes['is_trial'],
                $attributes['description'],
                $attributes['price'],
                $attributes['price_max']
            );
            $this->_incorrectModels[] = [
                'row'   => $attributes['service_name'],
                'error' => 'Обновлен'
            ];
        }

        return $divisionService;
    }

    /**
     * @param $dataRow
     * @param $company
     * @return array
     */
    private function getData($dataRow, $company)
    {
        $division = $this->getDivision(trim($dataRow[0][4]), $company);
        $insuranceCompany = $this->getInsuranceCompany(trim($dataRow[0][8]));

        if ($division) {
            $category_ids = self::getCategoryIds(trim($dataRow[0][5]), $division);
        }

        $price = intval(trim($dataRow[0][6]));

        return [
            'service_name'         => trim($dataRow[0][0]),
            'is_trial'             => trim($dataRow[0][1]) == '+' ? 1 : 0,
            'average_time'         => trim($dataRow[0][2]),
            'publish'              => self::getOption(trim($dataRow[0][3])),
            'division_id'          => $division ? $division->id : null,
            'category_ids'         => isset($category_ids) ? $category_ids : null,
            'price'                => $price,
            'price_max'            => null,
            'description'          => trim($dataRow[0][7]),
            'insurance_company_id' => $insuranceCompany ? $insuranceCompany->id : null,
        ];
    }

    /**
     * @param string $name
     * @param Company $company
     * @return Division
     */
    private function getDivision($name, Company $company)
    {
        /** @var Division $model */
        $model = Division::find()->company($company->id)->where(['name' => $name])->orderBy('status DESC')->one();

        if ($model === null) {
            throw new NotFoundException(
                Yii::t('app', 'Division {division} not found', ['division' => $name])
            );
        }

        return $model;
    }

    /**
     * @param string $name
     *
     * @return InsuranceCompany
     */
    private function getInsuranceCompany($name)
    {
        if (empty($name)) {
            return null;
        }

        /** @var InsuranceCompany $model */
        $model = InsuranceCompany::find()->where(['name' => $name])->one();

        if ($model === null) {
            throw new NotFoundException('InsuranceCompany not found');
        }

        return $model;
    }

    /**
     * @param $categories
     * @param $division
     * @return array
     */
    private static function getCategoryIds($categories, $division)
    {
        $names = explode(";", $categories);

        $category_ids = [];
        foreach ($names as $name) {
            $name = trim($name);

            if (empty($name)) {
                continue;
            }

            $category_id = ServiceCategory::find()
                ->select(['{{%service_categories}}.id'])
                ->where(['{{%service_categories}}.name' => $name])
                ->andWhere([
                    "OR",
                    ['type' => ServiceCategory::TYPE_CATEGORY_STATIC],
                    ['type' => ServiceCategory::TYPE_CATEGORY_DYNAMIC, 'company_id' => $division->company_id]
                ])
                ->scalar();

            if (!$category_id) {
                $category = new ServiceCategory([
                    'name'               => $name,
                    'parent_category_id' => $division->category_id,
                    'company_id'         => $division->company_id,
                    'type'               => ServiceCategory::TYPE_CATEGORY_DYNAMIC
                ]);
                $category->save();
                $category_ids[] = $category->id;
            } else {
                $category_ids[] = $category_id;
            }
        }

        return array_filter($category_ids);
    }
}
