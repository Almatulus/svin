<?php

namespace frontend\modules\order\search;

use core\helpers\order\OrderConstants;
use core\models\order\Order;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * OrderSearch represents the model behind the search form about `core\models\order\Order`.
 */
class OrderSearch extends Order
{
    public $from_date;
    public $to_date;
    public $source_id;
    public $division_service_id;
    public $service_categories;

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'status',
                    'company_customer_id',
                    'type',
                    'staff_id',
                    'division_id',
                    'referrer_id',
                    'number',
                    'created_user_id',
                    'source_id',
                    'division_service_id',
                    'is_paid'
                ],
                'integer'
            ],
            [['service_categories'], 'each', 'rule' => ['integer']],
            [['from_date', 'to_date'], 'datetime', 'format' => 'php:Y-m-d'],
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
        $query = self::find()
            ->permitted()
            ->joinWith(['staff', 'companyCustomer', 'createdUser']);

        $lastUpdateQuery = Order::find()
            ->select('{{%orders}}.id, MAX({{%order_history}}.created_time) as updated_time')
            ->permitted()
            ->joinWith('orderHistory', false)
            ->groupBy('{{%orders}}.id');

        $query->leftJoin(['upd' => $lastUpdateQuery], '{{%orders}}.id = upd.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'company_customer_id',
                    'created_time',
                    'updated_time' => [
                        'asc'  => ['upd.updated_time' => SORT_ASC],
                        'desc' => ['upd.updated_time' => SORT_DESC]
                    ],
                    'datetime',
                    'note',
                    'number',
                    'price',
                    'staff_id'     => [
                        'asc'  => ['{{%staffs}}.surname' => SORT_ASC, '{{%staffs}}.name' => SORT_ASC],
                        'desc' => ['{{%staffs}}.surname' => SORT_DESC, '{{%staffs}}.name' => SORT_DESC]
                    ],
                    'status'
                ],
                'defaultOrder' => ['datetime' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        // save search params in session, in case to export the data through actionExport
        $session = Yii::$app->session;
        $session->set('order_search_query', $params);

        if ( ! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }

        if ($this->status == OrderConstants::STATUS_DISABLED) {
            $query->canceled();
        } else {
            $query->andFilterWhere(['{{%orders}}.status' => $this->status]);
        }

        if (intval($this->source_id) === -1) {
            $query->andWhere(['{{%company_customers}}.source_id' => null]);
        } else {
            $query->andFilterWhere(['{{%company_customers}}.source_id' => $this->source_id]);
        }

        $query->andFilterWhere([
            '{{%orders}}.company_customer_id' => $this->company_customer_id,
            '{{%orders}}.referrer_id' => $this->referrer_id,
            '{{%orders}}.type' => $this->type,
            '{{%orders}}.datetime' => $this->datetime,
            '{{%orders}}.staff_id' => $this->staff_id,
            '{{%orders}}.division_id' => $this->division_id,
            '{{%orders}}.number' => $this->number,
            '{{%orders}}.is_paid' => $this->is_paid,
            '{{%orders}}.services_disabled' => $this->services_disabled
        ]);

        if (intval($this->created_user_id) === -1) {
            $query->andWhere(['{{%orders}}.created_user_id' => null]);
        } else {
            $query->andFilterWhere(['{{%orders}}.created_user_id' => $this->created_user_id]);
        }

        if ($this->division_service_id || $this->service_categories) {
            $service_query = Order::find()->distinct()
                ->joinWith('orderServices.divisionService.categories', false)
                ->andFilterWhere([
                    '{{%order_services}}.division_service_id' => $this->division_service_id,
                    '{{%service_categories}}.id'              => $this->service_categories
                ]);

            $query->innerJoin(['ser' => $service_query], 'ser.id = {{%orders}}.id');
        }

        $finishDate = $this->to_date
            ? ((new \DateTime($this->to_date))->modify('+1 day')->format('Y-m-d'))
            : null;
        $query->andFilterWhere(['>=', '{{%orders}}.datetime', $this->from_date]);
        $query->andFilterWhere(['<=', '{{%orders}}.datetime', $finishDate]);

        return $dataProvider;
    }
}
