<?php

namespace frontend\modules\admin\search;

use core\models\company\TariffPayment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TariffPaymentSearch represents the model behind the search form about `core\models\company\TariffPayment`.
 */
class TariffPaymentSearch extends TariffPayment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sum', 'company_id', 'period'], 'integer'],
            [['start_date', 'created_at'], 'safe'],
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
        $query = TariffPayment::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id'         => $this->id,
            'sum'        => $this->sum,
            'company_id' => $this->company_id,
            'period'     => $this->period,
            'start_date' => $this->start_date,
            'created_at' => $this->created_at,
        ]);

        return $dataProvider;
    }
}
