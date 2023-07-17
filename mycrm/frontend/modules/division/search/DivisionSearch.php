<?php

namespace frontend\modules\division\search;

use core\models\division\Division;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DivisionSearch represents the model behind the search form about `core\models\Division`.
 */
class DivisionSearch extends Division
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'city_id', 'status'], 'integer'],
            [['name', 'url', 'address', 'working_start', 'working_finish'], 'safe'],
            [['rating', 'latitude', 'longitude'], 'number'],
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
        $query = Division::find()->company()->permitted();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['status' => SORT_DESC, 'id' => SORT_DESC]),
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'company_id' => $this->company_id,
            'city_id' => $this->city_id,
            'status' => $this->status,
            'rating' => $this->rating,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'working_start' => $this->working_start,
            'working_finish' => $this->working_finish,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'address', $this->address]);

        return $dataProvider;
    }
}
