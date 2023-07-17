<?php

namespace core\forms\customer;

use core\helpers\DateHelper;
use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerSource;
use core\models\finance\CompanyCashflow;
use core\models\finance\query\CashflowQuery;
use core\models\order\Order;
use core\models\Staff;
use core\models\StaffSchedule;
use core\models\user\User;
use DateTime;
use Yii;
use yii\base\Model;

/**
 * @TODO Refactor. Move to ../forms folder
 * StatisticForm is for Statistics
 * @package core\forms\customer
 *
 * @property integer $staff
 * @property integer $user
 *
 * @property integer $from
 * @property integer $to
 * @property integer $difference
 * @property Order[] $orders
 * @property integer $totalCount
 * @property integer $totalRevenue
 * @property float $averageRevenue
 * @property StaffSchedule[] $schedules
 * @property float $occupancy
 * @property integer $repeatedCount
 * @property integer $singleCount
 *
 */
class StatisticForm extends Model
{
    public $division;
    public $staff;
    public $user;

    private $_from;
    private $_to;
    private $_difference;

    private $_orders;
    private $_totalCount;
    private $_repeatedCount;
    private $_totalRevenue;
    private $_averageRevenue;
    private $_schedules;
    private $_occupancy;

    private $_ordersCount = [];

    private $_income;
    private $_expense;

    const SCENARIO_GENERAL = 'general';

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['from', 'to'],
            self::SCENARIO_GENERAL => ['from', 'to', 'division', 'staff', 'user'],
        ];
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'string'],
            [['division', 'staff', 'user'], 'integer'],
        ];
    }

    /**
     * Form name is overridden in order to shorten the search url
     * @return string formName
     */
    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'division' => Yii::t('app', 'Division'),
            'from'     => Yii::t('app', 'From'),
            'to'       => Yii::t('app', 'To'),
        ];
    }

    // GETTERS and SETTERS

    public function setFrom($value)
    {
        $this->_from = $value;
    }

    public function setTo($value)
    {
        $this->_to = $value;
    }

    public function getFrom()
    {
        if (!$this->_from) {
            $this->_from = date("Y-m-d", strtotime($this->to . " -6 days"));
        }
        return $this->_from;
    }

    public function getTo()
    {
        if (!$this->_to) {
            $this->_to = date("Y-m-d");
        }
        return $this->_to;
    }

    /**
     * @return DateTime
     */
    public function getEndDateTime()
    {
        return (new DateTime($this->to))->modify("+1 day");
    }

    public function getDifference()
    {
        if (!$this->_difference) {
            $datetimeTo = new DateTime($this->to);
            $datetimeFrom = new DateTime($this->from);
            $this->_difference = $datetimeTo->diff($datetimeFrom)->days;
        }
        return $this->_difference;
    }

    public function getOrders()
    {
        if (!$this->_orders) {
            $query = Order::find()
                ->company()
                ->startFrom((new DateTime($this->from)))
                ->to($this->endDateTime);

            if ($this->division) {
                $query->division($this->division);
            }
            if ($this->staff) {
                $query->staff($this->staff);
            }
            if ($this->user) {
                $query->companyCustomerID($this->user);
            }

            $this->_orders = $query->all();
        }
        return $this->_orders;
    }

    public function getOrderQuery()
    {
        $query = Order::find()
            ->company(false)
            ->permitted()
            ->startFrom((new DateTime($this->from)))
            ->to($this->endDateTime->modify("-1 day"));

        if ($this->division) {
            $query->division($this->division);
        }
        if ($this->staff) {
            $query->staff($this->staff);
        }
        if ($this->user) {
            $query->companyCustomerID($this->user);
        }
        return $query;
    }

    public function getOrdersCount($column, $key)
    {
        if (!isset($this->_ordersCount[$column])) {
            $this->_ordersCount[$column] = $this->getOrderQuery()
                ->select(['crm_orders.' . $column, "COUNT(*) as count"])
                ->groupBy('crm_orders.' . $column)
                ->indexBy($column)
                ->asArray()
                ->all();
        }
        return isset($this->_ordersCount[$column][$key]['count']) ? $this->_ordersCount[$column][$key]['count'] : 0;
    }

    public function getTotalCount()
    {
        if (!$this->_totalCount) {
            $this->_totalCount = $this->getOrderQuery()->count();
        }
        return $this->_totalCount;
    }

    public function getRepeatedCount()
    {
        if (!$this->_repeatedCount) {
            $this->_repeatedCount = $this->getOrderQuery()
                ->select(["{{%orders}}.company_customer_id"])
                ->groupBy('{{%orders}}.company_customer_id')
                ->having('COUNT({{%orders}}.company_customer_id) > 1')
                ->count();
        }
        return $this->_repeatedCount;
    }

    public function getSingleCount()
    {
        return $this->totalCount - $this->repeatedCount;
    }

    public function getDisabledCount()
    {
        return $this->getOrdersCount('status', OrderConstants::STATUS_DISABLED)
            + $this->getOrdersCount('status', OrderConstants::STATUS_CANCELED);
    }

    public function getEnabledCount()
    {
        return $this->getOrdersCount('status', OrderConstants::STATUS_ENABLED);
    }

    public function getFinishedCount()
    {
        return $this->getOrdersCount('status', OrderConstants::STATUS_FINISHED);
    }

    public function getManualOrdersCount()
    {
        return $this->getOrdersCount('type', OrderConstants::TYPE_MANUAL);
    }

    public function getApplicationOrdersCount()
    {
        return $this->getOrdersCount('type', OrderConstants::TYPE_APPLICATION);
    }

    public function getSiteOrdersCount()
    {
        return $this->getOrdersCount('type', OrderConstants::TYPE_SITE);
    }

    /**
     * Get Revenue from Finished orders
     * @return int
     */
    public function getTotalRevenue()
    {
        if (!$this->_totalRevenue) {
            $this->_totalRevenue = 0;
            $this->_totalRevenue = $this->getOrderQuery()
                ->andWhere(['{{%orders}}.status' => OrderConstants::STATUS_FINISHED])
                ->sum('price');
        }
        return $this->_totalRevenue;
    }

    /**
     * Get average revenue from Finished orders
     * @return float|int
     */
    public function getAverageRevenue()
    {
        if (!$this->_averageRevenue) {
            $this->_averageRevenue = 0;
            if ($this->finishedCount != 0) {
                $this->_averageRevenue = $this->totalRevenue / $this->finishedCount;
            }
        }
        return $this->_averageRevenue;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSchedules()
    {
        if (!$this->_schedules) {

            $query = StaffSchedule::find()
                ->joinWith(['staff.divisions'])
                ->andWhere(':from <= crm_staff_schedules.datetime AND crm_staff_schedules.datetime < :to',
                    [
                        ':from' => (new DateTime($this->from))->format("Y-m-d"),
                        ":to"   => $this->endDateTime->format("Y-m-d")
                    ])
                ->andFilterWhere(['{{%staff_division_map}}.division_id' => Yii::$app->user->identity->permittedDivisions])
                ->andFilterWhere(['company_id' => Yii::$app->user->identity->company_id]);
            if ($this->division) {
                $query->andFilterWhere(['{{%divisions}}.id' => $this->division]);
            }
            if ($this->staff) {
                $query = $query->andFilterWhere(['crm_staff_schedules.staff_id' => $this->staff]);
            }
            if ($this->user) {
                $query = $query
                    ->joinWith('order.companyCustomer')
                    ->andFilterWhere(['crm_orders.company_customer_id' => $this->user]);
            }
            $this->_schedules = $query->all();
        }

        return $this->_schedules;
    }

    /**
     * @TODO Rewrite
     * Returns total time in minutes
     * @return int
     */
    public function getTotalWorkTime()
    {
        $schedules = StaffSchedule::find()
            ->joinWith(['staff.divisions'], false)
            ->andWhere(['<=', '{{%staff_schedules}}.end_at', $this->endDateTime->format("Y-m-d")])
            ->andWhere(['>=', '{{%staff_schedules}}.start_at', $this->from])
            ->andWhere([
                '{{%divisions}}.company_id' => Yii::$app->user->identity->company_id,
                '{{%staffs}}.status'        => Staff::STATUS_ENABLED
            ])
            ->andFilterWhere(['{{%staff_division_map}}.division_id' => Yii::$app->user->identity->permittedDivisions])
            ->andFilterWhere(['{{%staff_division_map}}.division_id' => $this->division])
            ->andFilterWhere(['{{%staff_schedules}}.staff_id' => $this->staff])
            ->all();
        return array_reduce($schedules, function ($duration, StaffSchedule $schedule) {
            return $duration + abs(strtotime($schedule->start_at) - strtotime($schedule->end_at)) / 60;
        }, 0);
    }

    /**
     * @TODO Rewrite
     * Returns ordered total time in minutes
     * @return int
     */
    public function getTotalOrderedTime()
    {
        return Order::find()
            ->joinWith(['staff.divisions'], false)
            ->andWhere(['<=', '{{%orders}}.datetime', $this->endDateTime->format("Y-m-d")])
            ->andWhere(['>=', '{{%orders}}.datetime', $this->from])
            ->andWhere([
                '{{%divisions}}.company_id' => Yii::$app->user->identity->company_id,
                '{{%staffs}}.status'        => Staff::STATUS_ENABLED,
                '{{%orders}}.status'        => OrderConstants::STATUS_FINISHED
            ])
            ->andFilterWhere(['{{%staff_division_map}}.division_id' => Yii::$app->user->identity->permittedDivisions])
            ->andFilterWhere(['{{%staff_division_map}}.division_id' => $this->division])
            ->andFilterWhere(['{{%orders}}.staff_id' => $this->staff])
            ->andFilterWhere(['{{%orders}}.company_customer_id' => $this->user])
            ->sum('duration');
    }

    /**
     * @return float|int
     */
    public function getOccupancy()
    {
        if (!$this->_occupancy) {
            $this->_occupancy = 0;

            $workTime = $this->getTotalWorkTime();
            $orderedTime = $this->getTotalOrderedTime() ?: 0;

            if ($workTime != 0) {
                $this->_occupancy = $orderedTime / $workTime;
            }
        }
        return $this->_occupancy;
    }

    /**
     * @param $mode
     * @return array
     */
    public function getPieMode($mode)
    {
        $pie = [];
        foreach ($this->orders as $key => $value) {
            /* @var $value Order */
            if (isset($pie[$value->$mode])) {
                $pie[$value->$mode]++;
            } else {
                $pie[$value->$mode] = 1;
            }
        }
        return $pie;
    }

    /**
     * @param $range
     * @return array
     */
    public function getRangedRevenue($range)
    {
        $data = [];

        $query = CompanyCashflow::find()
            ->select(['date', 'value'])
            ->andWhere(
                ['AND', ':startDate <= date', 'date <= :finishDate'],
                [':startDate' => $this->from, ':finishDate' => $this->endDateTime->format("Y-m-d")]
            )
            ->active()
            ->company(\Yii::$app->user->identity->company_id)
            ->income(false)
            ->permittedDivisions();

        if ($this->division) {
            $query->andFilterWhere(['{{%company_cashflows}}.division_id' => $this->division]);
        }
        if ($this->staff)
            $query = $query->andFilterWhere(['{{%company_cashflows}}.staff_id' => $this->staff]);
        if ($this->user) {
            $query = $query->andFilterWhere(['{{%company_cashflows}}.customer_id' => $this->user]);
        }

        $cashflows = $query->asArray()->all();
        $cashflows = \yii\helpers\ArrayHelper::index($cashflows, null, function ($element) {
            return Yii::$app->formatter->asDate($element['date'], 'php:Y-m-d');
        });

        foreach ($range as $key => $date) {
            $items = $cashflows[$date] ?? null;
            $sum = 0;
            if ($items) {
                foreach ($items as $index => $item) {
                    $sum += $item['value'];
                }
            }
            $data[] = $sum;
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function getProfit()
    {
        return $this->income - $this->expense;
    }

    /**
     * @return mixed
     */
    public function getExpense()
    {
        if (!$this->_expense) {
            $this->_expense = intval($this->getCashFlowQuery()->expense()->sum('value'));
        }
        return $this->_expense;
    }

    /**
     * @return mixed
     */
    public function getIncome()
    {
        if (!$this->_income) {
            $this->_income = $this->getCashFlowQuery()->income()->sum('value');
            $this->_income = $this->_income ?: 0;
        }
        return $this->_income;
    }

    /**
     * @return CashflowQuery
     */
    public function getCashFlowQuery()
    {
        /* @var $query CashflowQuery */
        $query = CompanyCashflow::find()
            ->range($this->from, $this->endDateTime->format("Y-m-d"), false)
            ->active()
            ->company(\Yii::$app->user->identity->company_id)
            ->permittedDivisions();

        $query->andFilterWhere(['{{%company_cashflows}}.division_id' => $this->division]);
        $query->andFilterWhere(['{{%company_cashflows}}.staff_id' => $this->staff]);
        $query->andFilterWhere(['{{%company_cashflows}}.customer_id' => $this->user]);

        return $query;
    }

    /**
     * @param string $countAttributeName
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCustomersBySource(string $countAttributeName = 'y')
    {
        $subQuery = CompanyCustomer::find()
            ->select(['source_id', "COUNT(*) as {$countAttributeName}"])
            ->company()
            ->joinWith('orders', false)
            ->andWhere(['{{%orders}}.id' => $this->getOrderQuery()->column()])
            ->active(true)
            ->groupBy(['source_id']);

        $data = CustomerSource::find()
            ->select([
                'id',
                'name',
                $countAttributeName
            ])
            ->where([
                'OR',
                ['type' => CustomerSource::TYPE_DEFAULT],
                [
                    'AND',
                    ['type' => CustomerSource::TYPE_DYNAMIC],
                    ['company_id' => Yii::$app->user->identity->company_id]
                ]
            ])
            ->leftJoin(['cs' => $subQuery], '{{%company_customer_sources}}.id = cs.source_id')
            ->andWhere("{$countAttributeName} > 0")
            ->asArray()
            ->all();

        $identifiedCustomers = array_sum(array_column($data, $countAttributeName));
        $totalCustomers = $this->getOrderQuery()->count();

        $data[] = [
            'name'              => Yii::t('app', 'Unknown'),
            $countAttributeName => $totalCustomers - $identifiedCustomers
        ];
        return $data;
    }

    /**
     * @param string $countAttributeName
     * @return array
     */
    public function getOrdersByCreator(string $countAttributeName = 'y')
    {
        $subQuery = Order::find()
            ->select([
                '{{%orders}}.created_user_id',
                "COUNT(*) as {$countAttributeName}"
            ])
            ->company()
            ->finished()
            ->andFilterWhere([
                '>=',
                '{{%orders}}.datetime',
                $this->_from
            ])
            ->andFilterWhere([
                '<=',
                '{{%orders}}.datetime',
                $this->_to
            ])
            ->andFilterWhere(['{{%orders}}.company_customer_id' => $this->user])
            ->groupBy('{{%orders}}.created_user_id');

        $data = User::find()
            ->joinWith(['staff.divisions', 'company'])
            ->select([
                '{{%staffs}}.id',
                '{{%users}}.company_id',
                '{{%staff_division_map}}.division_id',
                "coalesce({{%staffs}}.name, '') || ' ' || coalesce({{%staffs}}.surname, '') as staff_name",
                "coalesce({{%companies}}.head_name, '') || ' ' || coalesce({{%companies}}.head_surname, '') || ' ' || coalesce({{%companies}}.head_patronymic, '') as user_name",
                "{$countAttributeName}"
            ])
            ->andFilterWhere(['{{%staff_division_map}}.division_id' => $this->division])
            ->enabled()
            ->company()
            ->innerJoin(
                ['cs' => $subQuery],
                '{{%users}}.id = cs.created_user_id'
            )
            ->asArray()
            ->all();

        return array_map(function ($user) use ($countAttributeName) {
            $user_data = [];
            $user_data[$countAttributeName] = $user[$countAttributeName];
            $user_data['name'] = $user['staff_name'] == ' ' ? $user['user_name'] : $user['staff_name'];
            return $user_data;
        }, $data);
    }

    /**
     * @param string $countAttributeName
     * @return array
     */
    public function getOrdersByTypes(string $countAttributeName = 'y')
    {
        return [
            [
                'name'              => OrderConstants::getTypes()[OrderConstants::TYPE_MANUAL],
                $countAttributeName => $this->getManualOrdersCount(),
            ],
            [
                'name'              => OrderConstants::getTypes()[OrderConstants::TYPE_APPLICATION],
                $countAttributeName => $this->getApplicationOrdersCount(),
            ],
            [
                'name'              => OrderConstants::getTypes()[OrderConstants::TYPE_SITE],
                $countAttributeName => $this->getSiteOrdersCount(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'income',
            'expense',
            'profit',
            'averageRevenue',
            'occupancy' => function () {
                return number_format($this->occupancy * 100, 2, '.', ' ');
            },
            'totalCount',
            'disabledCount',
            'finishedCount',
            'enabledCount',
            'revenues'  => function () {
                $range = DateHelper::date_range($this->from, $this->to);
                $revenues = $this->getRangedRevenue($range);
                $data = [];
                foreach ($range as $index => $date) {
                    $data[] = [
                        'date'    => $date,
                        'revenue' => $revenues[$index] ?? 0
                    ];
                }
                return $data;
            },
            'sources'   => function () {
                return $this->getCustomersBySource("value");
            },
            'creators'  => function () {
                return $this->getOrdersByCreator("value");
            },
            'types'     => function () {
                return $this->getOrdersByTypes("value");
            }
        ];
    }
}
