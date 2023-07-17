<?php

namespace frontend\modules\finance\search;

use core\models\order\Order;
use Yii;
use yii\data\ActiveDataProvider;

class OrderReferrerSearch extends Order
{
    public $from;
    public $to;
    public $action;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->to   = date("Y-m-d");
        $this->from = date("Y-m-d");
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['from', 'to'], 'datetime', 'format' => 'php:Y-m-d'],
            [['referrer_id'], 'integer'],
            [['action'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'to'          => Yii::t('app', 'To date'),
            'from'        => Yii::t('app', 'From date'),
            'referrer_id' => Yii::t('app', 'Referrer'),
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
                     ->finished()
                     ->andWhere('referrer_id IS NOT NULL')
                     ->joinWith([
                         'companyCustomer.customer',
                         'staff',
                         'referrer'
                     ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['datetime' => SORT_DESC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $finishDate = new \DateTime($this->to);
        $finishDate->modify('+1 day');
        $query->andFilterWhere(['>=', 'datetime', $this->from]);
        $query->andFilterWhere(['<=', 'datetime', $finishDate->format('Y-m-d')]);
        $query->andFilterWhere(['referrer_id' => $this->referrer_id]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}