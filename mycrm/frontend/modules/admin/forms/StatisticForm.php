<?php

namespace frontend\modules\admin\forms;

use core\helpers\order\OrderConstants;
use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerRequest;
use core\models\finance\CompanyCashflow;
use core\models\order\Order;
use core\models\Staff;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * @property string $from
 * @property string $to
 */
class StatisticForm extends Model
{
    private $_from;
    private $_to;
    public $date_range;

    public function __construct()
    {
        $this->date_range = implode(' - ',[
            date('Y-m-d', strtotime('- 6 days')), // Week ago
            date('Y-m-d') // Today
        ]);

        list($this->_from, $this->_to) = explode(' - ', $this->date_range);

        return parent::__construct();
    }

    /**
     * Form name is overridden in order to shorten the search url
     * @return string formName
     */
    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['date_range'], 'required'],
        ];
    }

    public function beforeValidate()
    {
        if ($this->date_range) {
            list($this->_from, $this->_to) = explode(' - ', $this->date_range);
            $this->_from = date('Y-m-d', strtotime($this->_from));
            $this->_to = date('Y-m-d', strtotime("{$this->_to} + 1 day"));
        }

        return parent::beforeValidate();
    }

    public function getFrom()
    {
        return $this->_from;
    }

    public function getTo()
    {
        return $this->_to;
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $cashflowQuery = CompanyCashflow::find()
            ->select(['{{%company_cashflows}}.company_id', 'SUM(value) as income'])
            ->andWhere(['not in', '{{%company_cashflows}}.company_id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES])
            ->active()
            ->income(false)
            ->range($this->_from, $this->_to)
            ->groupBy('{{%company_cashflows}}.company_id');

        $customersQuery = CompanyCustomer::find()
            ->select(['company_id', 'COUNT(*) as customers_count'])
            ->andWhere(['not in', 'company_id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES])
            ->groupBy('company_id');

        $ordersQuery = Order::find()
            ->joinWith('division', false)
            ->select([
                'company_id',
                'COUNT(*) as orders_count',
                'COUNT(case when {{%orders}}.status=' . OrderConstants::STATUS_FINISHED . ' then 1 end) as finished',
                'COUNT(case when {{%orders}}.status=' . OrderConstants::STATUS_CANCELED . ' then 1 end) as canceled',
            ])
            ->andWhere(['not in', 'company_id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES])
            ->startFrom(new \DateTime($this->_from))
            ->to(new \DateTime(($this->to)))
            ->groupBy('company_id');

        $smsQuery = CustomerRequest::find()
            ->select(['company_id', 'COUNT(*) as sms_count'])
            ->where(['>=', 'created_time', $this->_from])
            ->andWhere(['<=', 'created_time', $this->_to])
            ->andWhere(['not in', 'company_id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES])
            ->groupBy('company_id');

        $staffQuery = Staff::find()
            ->select([
                'company_id',
                'COUNT(*) as staff_count',
                'COUNT(case when has_calendar=1 then 1 end) as staff_in_schedule'
            ])
            ->enabled()
            ->andWhere(['not in', 'company_id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES])
            ->joinWith('divisions', false)
            ->groupBy('company_id');

        $query = Company::find()->enabled()
            ->select([
                '{{%companies}}.id',
                '{{%companies}}.name',
                'staff_count',
                'staff_in_schedule',
                'customers_count',
                'income',
                'orders_count',
                'finished',
                'canceled',
                'sms_count'
            ])
            ->leftJoin(['cf' => $cashflowQuery], 'cf.company_id = {{%companies}}.id')
            ->leftJoin(['cu' => $customersQuery], 'cu.company_id = {{%companies}}.id')
            ->leftJoin(['ord' => $ordersQuery], 'ord.company_id = {{%companies}}.id')
            ->leftJoin(['sms' => $smsQuery], 'sms.company_id = {{%companies}}.id')
            ->leftJoin(['st' => $staffQuery], 'st.company_id = {{%companies}}.id')
            ->andWhere(['not in', '{{%companies}}.id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'income'            => [
                        'asc'  => new Expression('income ASC NULLS FIRST'),
                        'desc' => new Expression('income DESC NULLS LAST'),
                    ],
                    'name',
                    'staff_count'       => [
                        'asc'  => new Expression('staff_count ASC NULLS FIRST'),
                        'desc' => new Expression('staff_count DESC NULLS LAST'),
                    ],
                    'staff_in_schedule' => [
                        'asc'  => new Expression('staff_in_schedule ASC NULLS FIRST'),
                        'desc' => new Expression('staff_in_schedule DESC NULLS LAST'),
                    ],
                    'customers_count'   => [
                        'asc'  => new Expression('customers_count ASC NULLS FIRST'),
                        'desc' => new Expression('customers_count DESC NULLS LAST'),
                    ],
                    'orders_count'      => [
                        'asc'  => new Expression('orders_count ASC NULLS FIRST'),
                        'desc' => new Expression('orders_count DESC NULLS LAST'),
                    ],
                    'finished'          => [
                        'asc'  => new Expression('finished ASC NULLS FIRST'),
                        'desc' => new Expression('finished DESC NULLS LAST'),
                    ],
                    'canceled'          => [
                        'asc'  => new Expression('canceled ASC NULLS FIRST'),
                        'desc' => new Expression('canceled DESC NULLS LAST'),
                    ],
                    'sms_count'         => [
                        'asc'  => new Expression('sms_count ASC NULLS FIRST'),
                        'desc' => new Expression('sms_count DESC NULLS LAST'),
                    ]
                ],
                'defaultOrder' => ['income' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }


}