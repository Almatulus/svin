<?php

namespace frontend\search;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\order\Order;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * CustomerSearch represents the model behind the search form about `core\models\customer\CompanyCustomer`.
 */
class CustomerSearch extends CompanyCustomer
{
    const MODE_MOST_IMPORTANT = 1;
    const MODE_LEAST_IMPORTANT = 2;
    const MODE_NAME_ASC = 3;
    const MODE_NAME_DEC = 4;
    const MODE_MOST_PAID = 5;
    const MODE_LEAST_PAID = 6;
    const MODE_FIRST_VISIT = 7;
    const MODE_LAST_VISIT = 8;

    const SMS_RECEIVED = 0;
    const SMS_NOT_RECEIVED = 1;

    // TODO: I can shorten all the variable names in order to optimize search-URL
    public $sContact;
    public $sMode;
    public $sCategories;

    //Checkboxes
    public $sGender;
    public $sOnline;

    public $sStaff;
    public $sService;

    public $sBirthFrom;
    public $sBirthTo;

    public $sPaidMin;
    public $sPaidMax;

    public $sVisitCountMin;
    public $sVisitCountMax;

    public $sVisitedFrom;
    public $sVisitedTo;

    public $sNotVisitedFrom;
    public $sNotVisitedTo;

    public $sFirstVisitedFrom;
    public $sFirstVisitedTo;

    public $sSMSMode;
    public $sSMSFrom;
    public $sSMSTo;

    public $sIin;
    public $sCardNumber;

    public $moneySpent;
    public $lastVisit;

    public $active = true;

    public $sCity;
    public $sDivision;

    /**
     * Form name is overridden in order to shorten the search url
     * @return string formName
     */
    public function formName() {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sMode', 'sPaidMin', 'sPaidMax', 'sVisitCountMin',
                'sVisitCountMax', 'sSMSMode'], 'integer'],
            [['sCategories', 'sGender', 'sOnline', 'sStaff', 'sService', 'sIin', 'sCardNumber'], 'safe'],
            [['sContact', 'sVisitedFrom', 'sVisitedTo', 'sNotVisitedFrom', 'sNotVisitedTo',
                'sSMSFrom', 'sSMSTo','sCity', 'sFirstVisitedFrom', 'sFirstVisitedTo'], 'string'],
            [['sStaff', 'sCategories' ], 'each', 'rule' => ['integer']],
            [['sBirthFrom', 'sBirthTo'], 'date', 'format' => 'php:m-d'],
            ['sBirthFrom', 'compare', 'compareAttribute' => 'sBirthTo', 'operator' => '<',
                'when' => function($model) { return $model->sBirthTo && $model->sBirthFrom; }],
            ['sContact', 'trim'],
            ['sDivision', 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'sContact'    => Yii::t('app', 'Search Contact'),
            'sMode'       => Yii::t('app', 'Sort'),
            'sCategories' => Yii::t('app', 'Search Category'),
            'sGender'     => Yii::t('app', 'Search Gender'),
            'sStaff'      => Yii::t('app', 'Search Staff'),
            'sService'    => Yii::t('app', 'Search Service'),
            'sDivision'   => Yii::t('app', 'Division'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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

        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'moneySpent' => [
                        'asc' => [new Expression('SUM({{%orders}}.price) DESC NULLS LAST')],
                        'desc' => [new Expression('SUM({{%orders}}.price) ASC NULLS FIRST')  ],
                    ],
                    'lastVisit' => [
                        'asc' => [new Expression('MAX({{%orders}}.datetime) DESC NULLS LAST')],
                        'desc' => [new Expression('MAX({{%orders}}.datetime) ASC NULLS FIRSTT')],
                    ],
                    'name'
                ],
            ]
        ]);

        if (isset($params["sort"]) && is_scalar($params["sort"])) {
            $query->leftJoin(['{{%orders}}' => Order::find()->status(OrderConstants::STATUS_FINISHED)->division($this->sDivision)],
                '{{%orders}}.company_customer_id = {{%company_customers}}.id');
            $query->groupBy(['{{%company_customers}}.id', 'name']);
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (!empty($this->sBirthFrom)) {
            $query->andFilterWhere(['>=', "to_char(birth_date, 'MM-DD')", $this->sBirthFrom]);
        }

        if (!empty($this->sBirthTo)) {
            $query->andFilterWhere(['<=', "to_char(birth_date, 'MM-DD')", $this->sBirthTo]);
        }

        if (!empty($this->sCity)) {
            $query->andFilterWhere(['=', "city", $this->sCity]);
        }

        if (strlen($this->sContact)>0) {

            $terms = explode(' ', $this->sContact);
            foreach ($terms as $term) {
                $query->andFilterWhere([
                    'or',
                    ['like', "NULLIF(regexp_replace({{%customers}}.phone, '\D','','g'), '')", preg_replace("/[^0-9]/", '', trim($term))],
                    ['like', 'LOWER({{%customers}}.lastname)', mb_strtolower(trim($term))],
                    ['like', 'LOWER({{%customers}}.name)', mb_strtolower(trim($term))],
                    ['like', 'LOWER({{%customers}}.email)', mb_strtolower(trim($term))],
                ]);
            }
        }

        // Customer Category Filter
        if(self::filter($this->sCategories)) {
            $query->innerJoinWith('categories');
            $query->andWhere([
                '{{%customer_categories}}.id' => $this->sCategories
            ]);
        }

        // Staff, Service and ServiceCategory Filter
        if(self::filter($this->sStaff) || self::filter($this->sService)) {
            $query_service = CompanyCustomer::find()
                ->company()->active(true)
                ->select('{{%company_customers}}.id')
                ->join('INNER JOIN', '{{%orders}}', '{{%orders}}.company_customer_id = {{%company_customers}}.id')
                ->andFilterWhere(['{{%orders}}.division_id' => $this->sDivision]);

            if(self::filter($this->sStaff))
                $query_service->join('INNER JOIN', '{{%staffs}}', '{{%staffs}}.id = {{%orders}}.staff_id');
                $query_service->andFilterWhere(['IN','{{%staffs}}.id', $this->sStaff]);

            if(self::filter($this->sService)) {
                $query_service->join('INNER JOIN', '{{%order_services}}', '{{%orders}}.id = {{%order_services}}.order_id');
                $query_service->join('INNER JOIN', '{{%division_services}}', '{{%division_services}}.id = {{%order_services}}.division_service_id');
                $query_service->andFilterWhere(['IN','{{%division_services}}.id', $this->sService]);
            }

            $max_count = max([$this->sStaff,$this->sService]);
            $query_service->groupBy('{{%company_customers}}.id')
                ->andHaving(['>=','COUNT({{%company_customers}})',count($max_count)]);

            $query->andFilterWhere(['IN','{{%company_customers}}.id', $query_service]);
        }

        if (!empty($this->sDivision)) {
            $query->joinWith('orders')->andFilterWhere(['{{%orders}}.division_id' => $this->sDivision]);
        }

        // Gender Filter
        if(self::filter($this->sGender)) {
            $filterGender = ['or'];
            foreach ($this->sGender as $gender)
                $filterGender[] = ['=', 'gender', $gender];
            $query->andFilterWhere($filterGender);
        }

        // MoneySpent and VisitCount Filter
        if(!empty($this->sPaidMin) || !empty($this->sPaidMax) || !empty($this->sVisitCountMin) || !empty($this->sVisitCountMax)) {
            $query_number = CompanyCustomer::find()
                ->company()->active(true)
                ->select('{{%company_customers}}.id')
                ->andWhere(['{{%orders}}.status' => OrderConstants::STATUS_FINISHED])
                ->andFilterWhere(['{{%orders}}.division_id' => $this->sDivision])
                ->join('LEFT JOIN', '{{%orders}}', '{{%orders}}.company_customer_id = {{%company_customers}}.id');

            if(!empty($this->sPaidMin) || !empty($this->sPaidMax)) {
                if(!empty($this->sPaidMin)) $query_number->andHaving(['>=', "SUM({{%orders}}.price)", $this->sPaidMin]);
                if(!empty($this->sPaidMax)) $query_number->andHaving(['<=', "SUM({{%orders}}.price)", $this->sPaidMax]);
            }

            if(!empty($this->sVisitCountMin)) $query_number->andHaving(['>=', "COUNT({{%orders}})", $this->sVisitCountMin]);
            if(!empty($this->sVisitCountMax)) $query_number->andHaving(['<=', "COUNT({{%orders}})", $this->sVisitCountMax]);
            $query_number->addGroupBy('{{%company_customers}}.id');

            $query->andFilterWhere(['IN','{{%company_customers}}.id', $query_number]);
        }

        // Visited Filter
        if(!empty($this->sVisitedFrom) || !empty($this->sVisitedTo)) {
            $query_visited = CompanyCustomer::find()
                ->company()->active(true)
                ->select('{{%company_customers}}.id')
                ->joinWith('orders', false)
                ->andWhere(['{{%orders}}.status' => OrderConstants::STATUS_FINISHED])
                ->andFilterWhere(['{{%orders}}.division_id' => $this->sDivision]);
            if ( ! empty($this->sVisitedFrom)) {
                $query_visited->andFilterWhere([
                    '>=',
                    '{{%orders}}.datetime',
                    $this->sVisitedFrom . ' 00:00:00',
                ]);
            }
            if ( ! empty($this->sVisitedTo)) {
                $query_visited->andFilterWhere([
                    '<=',
                    '{{%orders}}.datetime',
                    $this->sVisitedTo . ' 23:59:59',
                ]);
            }
            $query_visited->addGroupBy('{{%company_customers}}.id');

            $query->andFilterWhere(['IN','{{%company_customers}}.id', $query_visited]);
        }

        if( ! empty($this->sFirstVisitedFrom) || !empty($this->sFirstVisitedTo)) {
            $query_first_orders = Order::find()
                ->company()
                ->select(new Expression("DISTINCT ON (company_customer_id) {{%orders}}.id"))
                ->andFilterWhere(['{{%orders}}.division_id' => $this->sDivision])
                ->status(OrderConstants::STATUS_FINISHED)
                ->orderBy('company_customer_id, id');

            $query_first_visited = CompanyCustomer::find()
                ->company()->active(true)
                ->select('{{%company_customers}}.id')
                ->joinWith('orders', false)
                ->andWhere(['{{%orders}}.status' => OrderConstants::STATUS_FINISHED])
                ->andFilterWhere(['IN','{{%orders}}.id', $query_first_orders]);

            if ( ! empty($this->sFirstVisitedFrom)) {
                $query_first_visited->andFilterWhere([
                    '>=',
                    '{{%orders}}.datetime',
                    $this->sFirstVisitedFrom . ' 00:00:00',
                ]);
            }
            if ( ! empty($this->sFirstVisitedTo)) {
                $query_first_visited->andFilterWhere([
                    '<=',
                    '{{%orders}}.datetime',
                    $this->sFirstVisitedTo . ' 23:59:59',
                ]);
            }
            $query_first_visited->addGroupBy('{{%company_customers}}.id');

            $query->andFilterWhere(['IN','{{%company_customers}}.id', $query_first_visited]);
        }

        // Not visited Filter
        if(!empty($this->sNotVisitedFrom) || !empty($this->sNotVisitedTo)) {
            $query_not_visited = CompanyCustomer::find()
                ->company()->active(true)
                ->select('{{%company_customers}}.id')
                ->join('LEFT JOIN', '{{%orders}}', '{{%orders}}.company_customer_id = {{%company_customers}}.id');
            if (!empty($this->sNotVisitedFrom)) $query_not_visited->andFilterWhere(['>=', '{{%orders}}.datetime', $this->sNotVisitedFrom]);
            if (!empty($this->sNotVisitedTo)) $query_not_visited->andFilterWhere(['<=', '{{%orders}}.datetime', $this->sNotVisitedTo]);
            $query_not_visited->addGroupBy('{{%company_customers}}.id');

            $query->andFilterWhere(['NOT IN','{{%company_customers}}.id', $query_not_visited]);
        }

        // SMS Filter
        if(isset($this->sSMSMode) && (!empty($this->sSMSFrom) || !empty($this->sSMSTo))) {
            $query_sms = CompanyCustomer::find()->company()->active(true);
            $query_sms->select('{{%company_customers}}.id');
            $query_sms->join('LEFT JOIN', '{{%customers}}', '{{%customers}}.id = {{%company_customers}}.customer_id');
            $query_sms->join('LEFT JOIN', '{{customer_requests}}', '{{customer_requests}}.customer_id = {{%customers}}.id');
            if(!empty($this->sSMSFrom)) $query_sms->andFilterWhere(['>=', '{{customer_requests}}.created_time', $this->sSMSFrom]);
            if(!empty($this->sSMSTo)) $query_sms->andFilterWhere(['<=', '{{customer_requests}}.created_time', $this->sSMSTo]);

            if($this->sSMSMode == 0)
                $query->andFilterWhere(['IN','{{%company_customers}}.id', $query_sms]);
            else
                $query->andFilterWhere(['NOT IN','{{%company_customers}}.id', $query_sms]);
        }

        if(!empty($this->sIin))
            $query->andFilterWhere(['=', '{{%customers}}.iin', $this->sIin]);
        if(!empty($this->sCardNumber))
            $query->andFilterWhere(['=', '{{%customers}}.id_card_number', $this->sCardNumber]);

        $query->active($this->active);

        return $dataProvider;
    }

    public static function getSMSMap() {
        return [
            self::SMS_RECEIVED => Yii::t('app','SMS received'),
            self::SMS_NOT_RECEIVED => Yii::t('app','SMS not received'),
        ];
    }

    private static function filter($input) {
        return isset($input) && is_array($input);
    }

}
