<?php

namespace frontend\modules\customer\search;

use core\models\customer\CustomerSubscription;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SubscriptionSearch represents the model behind the search form about `core\models\customer\CustomerSubscription`.
 */
class SubscriptionSearch extends Model
{
    public $purchased_start;
    public $purchased_end;
    public $first_visit_start;
    public $first_visit_end;
    public $expiry_date_start;
    public $expiry_date_end;
    public $price_start;
    public $price_end;
    public $company_customer_id;
    public $number_of_persons;
    public $quantity;
    public $status;
    public $type;
    public $key;
    public $price;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_customer_id', 'number_of_persons', 'quantity', 'status', 'type'], 'integer'],
            [['key'], 'safe'],
            [['price'], 'number'],
            ['status', 'in', 'range' => [CustomerSubscription::STATUS_NEW, CustomerSubscription::STATUS_ENABLED, CustomerSubscription::STATUS_DISABLED]],
            ['type', 'in', 'range' => [CustomerSubscription::TYPE_TIME, CustomerSubscription::TYPE_VISITS]],
            [['quantity', 'number_of_persons', 'price_end', 'price_start'], 'integer', 'min' => 0],
            [['purchased_start', 'purchased_end', 'first_visit_start', 
                'first_visit_end','expiry_date_start', 'expiry_date_end'], 'date', 'format' => "Y-m-d"],
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
        $query = CustomerSubscription::find()->company();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'company_customer_id' => $this->company_customer_id,
            'key' => $this->key,
            'number_of_persons' => $this->number_of_persons,
            'quantity' => $this->quantity,
            'status' => $this->status,
        ]);

        $query->andFilterWhere([
            'AND',
            ['>=', 'first_visit', $this->first_visit_start],
            ['<=', 'first_visit', $this->first_visit_end]
        ]);
        $query->andFilterWhere([
            'AND',
            ['>=', 'start_date', $this->purchased_start],
            ['<=', 'start_date', $this->purchased_end]
        ]);
        $query->andFilterWhere([
            'AND',
            ['>=', 'end_date', $this->expiry_date_start],
            ['<=', 'end_date', $this->expiry_date_end]
        ]);
        $query->andFilterWhere([
            'AND',
            ['>=', 'price', $this->price_start],
            ['<=', 'price', $this->price_end]
        ]);

        // $query->andFilterWhere(['like', 'key', $this->key]);

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return (new CustomerSubscription())->attributeLabels();
    }
}
