<?php

namespace frontend\modules\finance\search;

use core\models\customer\CompanyCustomer;
use core\models\order\query\OrderQuery;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class BalanceReportSearch extends Model
{
    public $from;
    public $to;
    public $type;
    public $customer_id;

    const TYPE_DEPOSIT = 0;
    const TYPE_DEBT = 1;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['type', 'default', 'value' => null],
            [['customer_id', 'type'], 'integer'],
            [['from', 'to'], 'date', 'format' => 'php:Y-m-d'],
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
        $query = CompanyCustomer::find()->with([
            'orders' => function (OrderQuery $query) {
                return $query->finished()
                    ->andWhere([
                        '!=',
                        '{{%orders}}.payment_difference',
                        0
                    ])
                    ->permitted()
                    ->orderBy('{{%orders}}.datetime');
            }
        ])->company()
            ->active(true)
            ->hasBalance();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['balance' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->type !== null) {
            if ($this->type == self::TYPE_DEPOSIT) {
                $query->andWhere(['>', "{{%company_customers}}.balance", 0]);
            } else {
                $query->andWhere(['<', "{{%company_customers}}.balance", 0]);
            }
        }

        if (!empty($this->from) || !empty($this->to)) {
            $query->joinWith('orders', false);
            $query->andFilterWhere(['>=', '{{%orders}}.datetime', $this->from]);
            $query->andFilterWhere(['<=', '{{%orders}}.datetime', $this->getToDate()]);
        }

        $query->andFilterWhere(['{{%company_customers}}.id' => $this->customer_id]);

        return $dataProvider;
    }

    /**
     * @return string
     */
    private function getToDate(): string
    {
        if (empty($this->to)) {
            return null;
        }

        return (new \DateTime($this->to))->modify("+1 day")->format("Y-m-d");
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}