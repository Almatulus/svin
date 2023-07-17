<?php

namespace core\forms\customer;

use core\forms\customer\statistic\StatisticStaff;
use core\helpers\order\OrderConstants;
use core\models\finance\CompanyCashflow;
use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @TODO Refactor. Move to ../forms folder
 * StatisticStaffForm is for Statistics Staff
 *
 * @property integer $from
 * @property integer $to
 * @property integer $difference
 * @property integer $division_id
 * @property integer[] $service_categories
 */
class StatisticStaffForm extends Model
{
    public $product_category_id;
    public $product_categories;
    public $product_id;
    public $service_category_id;
    public $division_id;
    public $service_categories;
    public $service_id;
    public $from;
    public $to;

    private $_difference;
    private $_scheduleData;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['division_id', 'service_id', 'service_category_id', 'product_category_id', 'product_id'], 'integer'],
            [['from', 'to'], 'datetime', 'format' => 'php:Y-m-d'],

            [['product_categories', 'service_categories'], 'each', 'rule' => ['integer']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->from = date("Y-m-d", strtotime($this->to . " -6 days"));
        $this->to = date("Y-m-d");
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'from'                => Yii::t('app', 'From'),
            'to'                  => Yii::t('app', 'To'),
            'service_category_id' => Yii::t('app', 'Category ID'),
            'product_category_id' => Yii::t('app', 'Product Category'),
            'division_id'         => Yii::t('app', 'Division ID'),
            'service_categories'  => Yii::t('app', 'Categories'),
            'service_id'          => Yii::t('app', 'Service ID'),
            'product_categories'  => Yii::t('app', 'Products'),
            'product_id'          => Yii::t('app', 'Product'),
        ];
    }

    /**
     * @return DateTime
     */
    private function getEndDateTime()
    {
        return (new DateTime($this->to))->modify("+1 day");
    }

    /**
     * @return integer
     */
    public function getDifference()
    {
        if (!$this->_difference) {
            $datetimeTo = new DateTime($this->to);
            $datetimeFrom = new DateTime($this->from);
            $this->_difference = $datetimeTo->diff($datetimeFrom)->days;
        }
        return $this->_difference;
    }

    private function getQuery()
    {
        $query = $this->getCommonStaffQuery()
            ->permitted()
            ->select([
                '{{%staffs}}.*',
                'revenue'
            ]);

        $subQuery = CompanyCashflow::find()
            ->select([
                'staff_id',
                'SUM(value) as revenue'
            ])
            ->andWhere(['receiver_mode' => CompanyCashflow::RECEIVER_STAFF])
            ->andWhere(":start_date <= date AND date < :finish_date")
            ->params([
                ":start_date"  => (new DateTime($this->from))->format("Y-m-d"),
                ":finish_date" => $this->getEndDateTime()->format("Y-m-d"),
            ])
            ->groupBy('staff_id');

        $query->leftJoin(['st' => $subQuery], '{{%staffs}}.id = st.staff_id');

        return $query;
    }

    /**
     * @return ActiveQuery
     */
    private function getCommonStaffQuery()
    {
        $query = StatisticStaff::find()
            ->distinct()
            ->enabled()
            ->timetableVisible();

        if ($this->division_id) {
            $query->joinWith('divisions');
            $query->andWhere(['{{%staff_division_map}}.division_id' => $this->division_id]);
        }

        return $query;
    }

    /**
     * @param int $staff_id
     * @return int
     */
    public function getOrderedTime(int $staff_id)
    {
        return $this->getScheduleData()[$staff_id]['ordered_time'] ?? 0;
    }

    /**
     * @return array
     */
    private function getScheduleData()
    {
        if ($this->_scheduleData === null) {
            $query = $this->getCommonStaffQuery()
                ->joinWith('orders', false)
                ->select([
                    '{{%staffs}}.id',
                    'SUM({{%orders}}.duration) as ordered_time',
                ])
                ->andWhere(":start_date <= {{%orders}}.datetime AND {{%orders}}.datetime < :finish_date",
                    [
                        ":start_date" => (new DateTime($this->from))->format("Y-m-d"),
                        ":finish_date" => $this->getEndDateTime()->format("Y-m-d"),
                    ])
                ->andWhere(['{{%orders}}.status' => OrderConstants::STATUS_FINISHED])
                ->groupBy('{{%staffs}}.id');

            $this->_scheduleData = $query->indexBy('id')->asArray()->all();
        }
        return $this->_scheduleData;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getTotalRevenue()
    {
        return $this->getQuery()->sum('revenue');
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);

        $query = $this->getQuery();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'revenue' => [
                        'asc'  => [new Expression("revenue ASC NULLS FIRST")],
                        'desc' => [new Expression("revenue DESC NULLS LAST")]
                    ],
                ],
                'defaultOrder' => ['revenue' => SORT_DESC]
            ]
        ]);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

    /**
     * @param StatisticStaff[] $models
     * @return array
     */
    public function getTop($models)
    {
        $maxRevenue = null;
        $minWorkedTime = null;
        $maxWorkedTime = null;

        if (sizeof($models) > 0) {
            $maxRevenue = $models[0];
            $minWorkedTime = $models[0];
            $maxWorkedTime = $models[0];
        }

        foreach ($models as $staff) {
            if ($maxRevenue->revenue < $staff->revenue) {
                $maxRevenue = $staff;
            }

            if ($minWorkedTime->getOrderedTime() > $staff->getOrderedTime()) {
                $minWorkedTime = $staff;
            }

            if ($maxWorkedTime->getOrderedTime() < $staff->getOrderedTime()) {
                $maxWorkedTime = $staff;
            }
        }

        return [
            'maxRevenue'    => $maxRevenue,
            'minWorkedTime' => $minWorkedTime,
            'maxWorkedTime' => $maxWorkedTime,
        ];
    }
}
