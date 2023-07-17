<?php

namespace api\modules\v2\search\customer;

use core\models\customer\CompanyCustomer;
use core\models\order\Order;
use core\models\order\query\OrderQuery;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class CustomerSearch extends CompanyCustomer
{
    public $term;
    public $is_active = true;
    public $name;
    public $lastname;
    public $patronymic;
    public $iin;
    public $id_card_number;
    public $phone;
    public $email;

    public $categories;
    public $staff;
    public $services;

    public $birthFrom;
    public $birthTo;

    public $paidMin;
    public $paidMax;

    public $visitCountMin;
    public $visitCountMax;

    public $visitedFrom;
    public $visitedTo;

    public $firstVisitedFrom;
    public $firstVisitedTo;

    public $smsMode;
    public $smsFrom;
    public $smsTo;

    public $city;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'term', 'name', 'lastname', 'patronymic', 'phone', 'email'], 'string'],
            [['iin', 'id_card_number', 'smsMode', 'paidMin', 'paidMax', 'visitCountMin', 'visitCountMax'], 'integer'],

            [['visitedFrom', 'visitedTo', 'smsFrom', 'smsTo', 'city', 'firstVisitedFrom', 'firstVisitedTo'], 'string'],

            [['birthFrom', 'birthTo'], 'date', 'format' => 'php:m-d'],
            [['firstVisitedFrom', 'firstVisitedTo'], 'date', 'format' => 'php:Y-m-d'],

            [
                'firstVisitedFrom',
                'compare',
                'compareAttribute' => 'firstVisitedTo',
                'operator'         => '<=',
                'when'             => function ($model) {
                    return $model->firstVisitedFrom && $model->firstVisitedTo;
                }
            ],
            [
                'birthFrom',
                'compare',
                'compareAttribute' => 'birthTo',
                'operator'         => '<=',
                'when'             => function ($model) {
                    return $model->birthTo && $model->birthFrom;
                }
            ],

            [['categories', 'services', 'staff'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = CompanyCustomer::find()->company()->joinWith('customer');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'name'       => [
                        'asc'  => [
                            '{{%customers}}.lastname'   => SORT_ASC,
                            '{{%customers}}.name'       => SORT_ASC,
                            '{{%customers}}.patronymic' => SORT_ASC,
                        ],
                        'desc' => [
                            '{{%customers}}.lastname'   => SORT_DESC,
                            '{{%customers}}.name'       => SORT_DESC,
                            '{{%customers}}.patronymic' => SORT_DESC,
                        ],
                    ],
                    'moneySpent' => [
                        'asc'  => [new Expression('SUM({{%orders}}.price) DESC NULLS LAST')],
                        'desc' => [new Expression('SUM({{%orders}}.price) ASC NULLS FIRST')],
                    ],
                ],
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        if (!empty($this->term)) {
            $sanitizedTerm = trim(str_replace('_', '', $this->term));
            $terms = explode(' ', $sanitizedTerm);
            foreach ($terms as $term) {
                $query->andFilterWhere([
                    'or',
                    ['like', 'lower({{%customers}}.name)', mb_strtolower($term)],
                    ['like', 'lower({{%customers}}.lastname)', mb_strtolower($term)],
                    ['like', '{{%customers}}.email', $term],
                    ['like', '{{%customers}}.phone', $term],
                    ['like', '{{%customers}}.iin', $term],
                    ['like', '{{%customers}}.id_card_number', $term],
                ]);
            }
        }

        if ($this->is_active !== null) {
            $query->active($this->is_active);
        }

        $full_name = $this->lastname . " " . $this->name;
        $query->andFilterWhere([
            'OR',
            [
                'like', 'lower({{%customers}}.name)', mb_strtolower($full_name)
            ],
            [
                'AND',
                ['like', 'lower({{%customers}}.name)', $this->name ? mb_strtolower($this->name) : null],
                ['like', 'lower({{%customers}}.lastname)', $this->lastname ? mb_strtolower($this->lastname) : null],
                ['like', 'lower({{%customers}}.patronymic)', $this->patronymic ? mb_strtolower($this->patronymic) : null],
                ['like', '{{%customers}}.email', $this->email],
                ['=', '{{%customers}}.iin', $this->iin],
                ['=', '{{%customers}}.id_card_number', $this->id_card_number],
            ]
        ]);

        $query->andFilterWhere(['like', '{{%customers}}.phone', $this->phone]);

        if (isset($params["sort"]) && is_scalar($params["sort"])) {
            $query->leftJoin(['{{%orders}}' => Order::find()->finished()],
                '{{%orders}}.company_customer_id = {{%company_customers}}.id');
            $query->groupBy([
                '{{%company_customers}}.id',
                '{{%customers}}.name',
                '{{%customers}}.lastname',
                '{{%customers}}.patronymic'
            ]);
        }

        $query->andFilterWhere(['>=', "to_char(birth_date, 'MM-DD')", $this->birthFrom]);
        $query->andFilterWhere(['<=', "to_char(birth_date, 'MM-DD')", $this->birthTo]);
        $query->andFilterWhere(["city" => $this->city]);

        if ($this->categories) {
            $query->innerJoinWith('categories')->andWhere([
                '{{%customer_categories}}.id' => $this->categories
            ]);
        }

        if ($this->staff || $this->services) {
            $query->innerJoinWith([
                'orders' => function (OrderQuery $query) {
                    return $query->finished();
                },
                'orders.orderServices'
            ])->andFilterWhere([
                '{{%orders}}.staff_id'                    => $this->staff,
                '{{%order_services}}.division_service_id' => $this->services
            ]);
        }

        if (!empty($this->paidMin) || !empty($this->paidMax)
            || !empty($this->visitCountMin) || !empty($this->visitCountMax)
            || !empty($this->visitedFrom) || !empty($this->visitedTo)) {

            $query_number = CompanyCustomer::find()
                ->select('{{%company_customers}}.id')
                ->company()
                ->innerJoinWith([
                    'orders' => function (OrderQuery $query) {
                        return $query->finished();
                    }
                ], false)->active(true);


            if (!empty($this->visitedFrom)) {
                $query_number->andFilterWhere(['>=', '{{%orders}}.datetime', $this->visitedFrom . ' 00:00:00']);
            }
            if (!empty($this->visitedTo)) {
                $query_number->andFilterWhere(['<=', '{{%orders}}.datetime', $this->visitedTo . ' 24:00:00']);
            }

            if (!empty($this->paidMin)) {
                $query_number->andHaving(['>=', "SUM({{%orders}}.price)", $this->paidMin]);
            }
            if (!empty($this->paidMax)) {
                $query_number->andHaving(['<=', "SUM({{%orders}}.price)", $this->paidMax]);
            }

            if (!empty($this->visitCountMin)) {
                $query_number->andHaving(['>=', "COUNT({{%orders}})", $this->visitCountMin]);
            }
            if (!empty($this->visitCountMax)) {
                $query_number->andHaving(['<=', "COUNT({{%orders}})", $this->visitCountMax]);
            }


            $query_number->groupBy('{{%company_customers}}.id');

            $query->innerJoin(['qn' => $query_number], 'qn.id = {{%company_customers}}.id');
        }

        if (!empty($this->firstVisitedFrom) || !empty($this->firstVisitedTo)) {
            $query_first_orders = Order::find()->distinct()
                ->select(['{{%orders}}.company_customer_id'])
                ->company()
                ->finished()
                ->groupBy('{{%orders}}.company_customer_id');

            if (!empty($this->firstVisitedFrom)) {
                $query_first_orders->andHaving(['>=', 'MIN(datetime)', $this->firstVisitedFrom . " 00:00:00"]);
            }

            if (!empty($this->firstVisitedTo)) {
                $query_first_orders->andHaving(['<=', 'MIN(datetime)', $this->firstVisitedTo . " 24:00:00"]);
            }

            $query->innerJoin(['fo' => $query_first_orders], 'fo.company_customer_id = {{%company_customers}}.id');
        }

        if (isset($this->smsMode) && (!empty($this->smsFrom) || !empty($this->smsTo))) {
            $query_sms = CompanyCustomer::find()->company()->active(true);
            $query_sms->select('{{%company_customers}}.id');
            $query_sms->join('LEFT JOIN', '{{%customers}}', '{{%customers}}.id = {{%company_customers}}.customer_id');
            $query_sms->join('LEFT JOIN', '{{customer_requests}}',
                '{{customer_requests}}.customer_id = {{%customers}}.id');
            if (!empty($this->smsFrom)) {
                $query_sms->andFilterWhere(['>=', '{{customer_requests}}.created_time', $this->smsFrom]);
            }
            if (!empty($this->smsTo)) {
                $query_sms->andFilterWhere(['<=', '{{customer_requests}}.created_time', $this->smsTo]);
            }

            if ($this->smsMode == 0) {
                $query->andFilterWhere(['IN', '{{%company_customers}}.id', $query_sms]);
            } else {
                $query->andFilterWhere(['NOT IN', '{{%company_customers}}.id', $query_sms]);
            }
        }

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}