<?php

namespace api\modules\v2\search\customer;

use core\helpers\order\OrderConstants;
use core\models\order\Order;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class LostCustomerSearch extends CustomerSearch
{
    public $number_of_days = 30;
    public $division;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['number_of_days', 'division'], 'integer'],
        ];

        return array_merge(parent::rules(), $rules);
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
        /** @var ActiveQuery $query */
        $query = parent::search($params)->query;

        $date = date("Y-m-d", strtotime("-{$this->number_of_days} days"));

        $subQuery = Order::find()
            ->select(['company_customer_id', 'MAX(datetime) as max_date'])
            ->where(['crm_orders.status' => OrderConstants::STATUS_FINISHED])
            ->company(false)
            ->groupBy('company_customer_id');

        if ($this->division) {
            $subQuery->joinWith('staff.divisions', false)
                ->andFilterWhere(['{{%divisions}}.id' => $this->division]);
        }

        $query
            ->leftJoin(['ord' => $subQuery], 'crm_company_customers.id = ord.company_customer_id')
            ->andWhere(['<=', 'max_date', $date])
            ->orderBy('max_date DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

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