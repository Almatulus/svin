<?php

namespace core\forms\statistic;

use core\models\order\Order;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class InsuranceStatForm extends Model
{
    public $from;
    public $to;
    public $insurance_company_id;
    public $service_id;
    public $staff_id;

    public $cashflow_date;
    public $customer_name;
    public $customer_policy;
    public $discount;
    public $discount_sum;
    public $insurance_company;
    public $quantity;
    public $price;
    public $service_name;
    public $staff_name;
    public $sum;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->to = date("Y-m-d");
        $this->from = date("Y-m-d", strtotime($this->to . " -6 days"));
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['from', 'date', 'format' => 'Y-m-d'],

            ['to', 'date', 'format' => 'Y-m-d'],
//            ['to', 'compare', 'compareAttribute' => 'from', 'operator' => '>'],

            ['insurance_company_id', 'integer'],
            ['service_id', 'integer'],
            ['staff_id', 'integer'],
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Order::find()
            ->distinct()
            ->permitted()
            ->finished()
            ->andWhere('{{%orders}}.insurance_company_id IS NOT NULL')
            ->joinWith([
                'companyCustomer.customer',
                'insuranceCompany',
                'orderServices',
                'staff',
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'datetime',
                    'customer_name'     => [
                        'asc'  => ['{{%customers}}.lastname' => SORT_ASC, '{{%customers}}.name' => SORT_ASC],
                        'desc' => ['{{%customers}}.lastname' => SORT_DESC, '{{%customers}}.name' => SORT_DESC],
                    ],
                    'customer_policy'   => [
                        'asc'  => ['{{%company_customers}}.insurance_policy_number' => SORT_ASC],
                        'desc' => ['{{%company_customers}}.insurance_policy_number' => SORT_DESC],
                    ],
//                    'discount'          => [
//                        'asc'  => ['{{%order_services}}.discount' => SORT_ASC],
//                        'desc' => ['{{%order_services}}.discount' => SORT_DESC],
//                    ],
//                    'discount_sum' => [
//                        'asc' => [new Expression('{{%order_services}}.price * {{%order_services}}.discount / 100 ASC')],
//                        'desc' => [new Expression('{{%order_services}}.price * {{%order_services}}.discount / 100 DESC')],
//                    ],
                    'insurance_company' => [
                        'asc'  => ['{{%insurance_companies}}.name' => SORT_ASC],
                        'desc' => ['{{%insurance_companies}}.name' => SORT_DESC],
                    ],
//                    'quantity'          => [
//                        'asc'  => ['{{%order_services}}.quantity' => SORT_ASC],
//                        'desc' => ['{{%order_services}}.quantity' => SORT_DESC],
//                    ],
//                    'price'             => [
//                        'asc'  => ['{{%order_services}}.price' => SORT_ASC],
//                        'desc' => ['{{%order_services}}.price' => SORT_DESC],
//                    ],
                    'staff_name'        => [
                        'asc'  => ['{{%staffs}}.surname' => SORT_ASC, '{{%staffs}}.name' => SORT_ASC],
                        'desc' => ['{{%staffs}}.surname' => SORT_DESC, '{{%staffs}}.name' => SORT_DESC],
                    ],
//                    'service_name'      => [
//                        'asc'  => ['{{%division_services}}.service_name' => SORT_ASC],
//                        'desc' => ['{{%division_services}}.service_name' => SORT_DESC],
//                    ],
                    'price'
                ],
                'defaultOrder' => ['datetime' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere([
            '{{%orders}}.insurance_company_id'        => $this->insurance_company_id,
            '{{%order_services}}.division_service_id' => $this->service_id,
            '{{%orders}}.staff_id'                    => $this->staff_id,
        ]);
        $query->andFilterWhere(["<=", '{{%orders}}.datetime', $this->to ? ($this->to . " 24:00:00") : null]);
        $query->andFilterWhere([">=", '{{%orders}}.datetime', $this->from]);

        return $dataProvider;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return "";
    }
}