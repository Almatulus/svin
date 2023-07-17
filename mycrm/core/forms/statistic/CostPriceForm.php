<?php

namespace core\forms\statistic;

use core\models\division\DivisionService;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CostPriceForm extends Model
{
    public $name;
    public $category_id;
    public $division_id;

    public function rules()
    {
        return [
            ['name', 'string', 'max' => 255],
            ['category_id', 'integer'],
            ['division_id', 'integer']
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = DivisionService::find()
            ->company(null, false)
            ->permitted(false)
            ->joinWith('products.product', false)
            ->select([
                '{{%division_services}}.*',
                'SUM({{%warehouse_product}}.purchase_price * {{%division_service_products}}.quantity) as products_sum'
            ])
            ->deleted(false)
            ->groupBy('{{%division_services}}.id')
            ->asArray();


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['service_name' => SORT_ASC]
            ]
        ]);

        $this->load($params);

        if ($this->validate()) {
            $query->andFilterWhere(['{{%divisions}}.id' => $this->division_id]);
            $query->joinWith('categories', false)->andFilterWhere([
                '{{%service_categories}}.id' => $this->category_id
            ]);
            $query->andFilterWhere(['ilike', '{{%division_services}}.service_name', $this->name]);
        }

        return $dataProvider;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return "";
    }

    /**
     * @param ActiveDataProvider $dataProvider
     * @return array
     */
    public function getData(ActiveDataProvider $dataProvider)
    {
        /** @var \core\models\Staff[] $staff */
        $staff = \core\models\Staff::find()
            ->joinWith('staffPayrolls.payroll')
            ->company()
            ->permitted()
            ->division($this->division_id, false)
            ->enabled()
            ->all();

        $currentDate = date("Y-m-d");
        $data = [];
        foreach ($dataProvider->models as $key => $serviceData) {
            $data[$serviceData['id']]['avg_staff_share'] = 0;

            $employerWithPayroll = 0;
            foreach ($staff as $employer) {
                $selectedEmployerPayroll = current($employer->staffPayrolls);

                foreach ($employer->staffPayrolls as $employerPayroll) {
                    if ($employerPayroll->started_time < $currentDate
                        && $employerPayroll->started_time > $selectedEmployerPayroll->started_time) {
                        $selectedEmployerPayroll = $employerPayroll;
                    }
                }

                $data[$serviceData['id']]['avg_staff_share'] += $selectedEmployerPayroll
                    ? $selectedEmployerPayroll->payroll->getServicePercent($serviceData['id'])
                    : 0;

                if ($selectedEmployerPayroll) {
                    $employerWithPayroll++;
                }
            }

            $data[$serviceData['id']]['avg_staff_share'] =
                $employerWithPayroll ? round($data[$serviceData['id']]['avg_staff_share'] / $employerWithPayroll,
                    2) : 0;
            $data[$serviceData['id']]['staff_count'] = $employerWithPayroll;
            $data[$serviceData['id']]['cost_price'] = ($serviceData['price'] * (100 - $data[$serviceData['id']]['avg_staff_share']) / 100)
                - $serviceData['products_sum'];
        }

        return $data;
    }
}