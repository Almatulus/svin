<?php

namespace common\components\parsers;

use core\models\company\Company;
use core\models\division\Division;
use core\models\warehouse\Category;
use core\models\warehouse\Manufacturer;
use core\models\warehouse\Product;
use core\models\warehouse\ProductType;
use core\models\warehouse\ProductUnit;
use core\services\TransactionManager;
use Exception;
use PHPExcel_Worksheet;
use Yii;

class ProductParser extends BaseParser
{
    private $types;
    private $units;
    private $manufacturers;
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
     * @throws \yii\db\Exception
     */
    public function parse(PHPExcel_Worksheet $sheet, Company $company)
    {
        $this->units = ProductUnit::find()
            ->select(['id', 'name'])
            ->asArray()
            ->indexBy('name')
            ->all();
        $this->manufacturers = Manufacturer::find()
            ->select(['id', 'name'])
            ->where(['company_id' => $company->id])
            ->asArray()
            ->indexBy('name')
            ->all();
        $this->types = ProductType::find()
            ->indexBy('id')
            ->all();

        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {

            $dataRow = $sheet->rangeToArray(
                'A' . $row . ':M' . $row,
                null,
                true,
                false
            );

            Yii::$app->session->set(
                'progress',
                number_format($row / $highestRow * 100)
            );

            if (empty($dataRow[0][0])) {
                continue;
            }

            $this->transaction_manager->execute(function () use (
                $dataRow,
                $row,
                $company
            ) {

                $data = self::getData($dataRow, $company);
                $product = new Product();
                $product->setAttributes($data);

                if (!$product->save()) {
                    $this->_incorrectModels[] = [
                        'row'   => $row - 1,
                        'error' => array_values($product->firstErrors)[0]
                    ];
                    throw new \DomainException(array_values($product->firstErrors)[0]);
                } else {

                    if (!empty($data['types'])) {
                        $product->unlinkAll('productTypes', true);
                        foreach ($data['types'] as $typeId) {
                            if (isset($this->types[$typeId])) {
                                $product->link('productTypes', $this->types[$typeId]);
                            }
                        }
                    }

                    $this->_savedCounter++;
                }
            });
        }
    }

    /**
     * @param $dataRow
     * @param $company
     * @return array
     */
    private function getData($dataRow, $company)
    {
        $division = self::getDivision(trim($dataRow[0][1]), $company);
        $category = self::getCategory(trim($dataRow[0][4]), $company);
        $unit_id = $this->getUnit(trim($dataRow[0][6]));

        return [
            'name'            => trim($dataRow[0][0]),
            'division_id'     => $division ? $division->id : null,
            'types'           => self::getTypes(trim($dataRow[0][2]), trim($dataRow[0][3])),
            'category_id'     => $category ? $category->id : null,
            'price'           => trim($dataRow[0][5]),
            'unit_id'         => $unit_id,
            'quantity'        => trim($dataRow[0][7]),
            'min_quantity'    => trim($dataRow[0][8]),
            'manufacturer_id' => $this->getManufacturerID(trim($dataRow[0][9]), $company),
            'sku'             => trim($dataRow[0][10]),
            'barcode'         => trim($dataRow[0][11]),
            'description'     => trim($dataRow[0][12]),
        ];
    }

    /**
     * @param $forSale
     * @param $forUse
     * @return array
     */
    private static function getTypes($forSale, $forUse)
    {
        $types = [];
        if (self::getOption($forSale)) {
            $types[] = Product::TYPE_FOR_SALE;
        }
        if (self::getOption($forUse)) {
            $types[] = Product::TYPE_FOR_USE;
        }
        return $types;
    }


    /**
     * @param $name
     * @param $company
     * @return array|null|\yii\db\ActiveRecord
     */
    private static function getDivision($name, $company)
    {
        return Division::find()->where([
            'company_id' => $company->id,
            'name'       => $name
        ])->one();
    }

    /**
     * @param $name
     * @param $company
     * @return Category|array|null|\yii\db\ActiveRecord
     */
    private static function getCategory($name, $company)
    {
        $category = Category::find()->where([
            'name'       => $name,
            'company_id' => $company->id
        ])->one();

        if (!$category) {
            $category = new Category([
                'name'       => $name,
                'company_id' => $company->id
            ]);
            $category->save();
        }
        return $category;
    }

    private function getManufacturerID($name, $company)
    {
        if (isset($this->manufacturers[$name])) {
            return $this->manufacturers[$name]['id'];
        }

        $manufacturer = new Manufacturer([
            'name'       => $name,
            'company_id' => $company->id
        ]);

        if ($manufacturer->save()) {
            $this->manufacturers[$name] = [
                'id' => $manufacturer->id
            ];
            return $manufacturer->id;
        }

        return null;
    }

    /**
     * @param $name
     * @return null
     */
    private function getUnit($name)
    {
        if (isset($this->units[$name])) {
            return $this->units[$name]['id'];
        }
        return null;
    }
}