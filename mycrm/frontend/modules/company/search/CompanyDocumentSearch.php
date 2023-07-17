<?php

namespace frontend\modules\company\search;

use core\models\order\OrderDocumentTemplate;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use core\models\company\CompanyDocument;

/**
 * CompanyDocumentSearch represents the model behind the search form about `core\models\company\CompanyDocument`.
 */
class CompanyDocumentSearch extends OrderDocumentTemplate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'path'], 'safe'],
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
        $query = OrderDocumentTemplate::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id'         => $this->id,
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
