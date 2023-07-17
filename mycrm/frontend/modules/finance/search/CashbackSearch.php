<?php

namespace frontend\modules\finance\search;

use core\models\company\Cashback;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CashbackSearch represents the model behind the search form about `core\models\company\Cashback`.
 */
class CashbackSearch extends Cashback
{
    public $from;
    public $to;
    public $minAmount;
    public $maxAmount;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_customer_id', 'type', 'minAmount', 'maxAmount'], 'integer'],
            [['from', 'to'], 'date', 'format' => 'php:Y-m-d'],
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
        $query = Cashback::find()->company()->enabled();

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
            'id'                  => $this->id,
            'company_customer_id' => $this->company_customer_id,
            'type'                => $this->type,
            'amount'              => $this->amount,
            'percent'             => $this->percent,
        ]);

        $query->andFilterWhere([">=", 'created_at', $this->from]);
        $query->andFilterWhere(["<=", 'created_at', $this->to ? ($this->to . " 24:00:00") : null]);
        $query->andFilterWhere([">=", 'amount', $this->minAmount]);
        $query->andFilterWhere(["<=", 'amount', $this->maxAmount]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        $labels = parent::attributeLabels();
        $labels['from'] = \Yii::t('app', 'From');
        $labels['to'] = \Yii::t('app', 'To');
        $labels['minAmount'] = \Yii::t('app', 'Minimum sum');
        $labels['maxAmount'] = \Yii::t('app', 'Maximum sum');
        return $labels;
    }
}
