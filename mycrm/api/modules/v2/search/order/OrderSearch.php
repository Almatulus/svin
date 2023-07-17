<?php

namespace api\modules\v2\search\order;

use core\helpers\order\OrderConstants;
use core\models\order\Order;
use yii\data\ActiveDataProvider;

class OrderSearch extends Order
{
    public $from;
    public $to;

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
                ],
                'integer'
            ],
            [['from', 'to'], 'datetime', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @param bool $pagination
     * @return ActiveDataProvider
     */
    public function search($params, $pagination = true)
    {
        $query = self::find()->company()->permitted();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'datetime',
                    'division_id'
                ],
                'defaultOrder' => ['datetime' => SORT_DESC]
            ]
        ]);

        if (!$pagination) {
            $dataProvider->setPagination($pagination);
        }

        $this->load($params);

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

        $query->andFilterWhere([
            '{{%orders}}.company_customer_id' => $this->company_customer_id,
            '{{%orders}}.type'                => $this->type,
            '{{%orders}}.staff_id'            => $this->staff_id,
        ]);

        $query->andFilterWhere(['>=', 'datetime', $this->from]);

        if ( ! empty($this->to)) {
            $finishDate = new \DateTime($this->to);
            $finishDate->modify('+1 day');
            $query->andFilterWhere([
                '<=',
                'datetime',
                $finishDate->format('Y-m-d'),
            ]);
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