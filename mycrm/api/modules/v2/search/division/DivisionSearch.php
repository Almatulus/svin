<?php

namespace api\modules\v2\search\division;

use core\models\division\Division;
use yii\data\ActiveDataProvider;

/**
 * DivisionSearch represents the model behind the search form about `core\models\Division`.
 */
class DivisionSearch extends Division
{
    public $is_open;
    public $has_payment;

    public $division_id;
    public $price_start;
    public $price_finish;
    public $category_id;
    public $service_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id', 'company_id', 'city_id', 'price_start', 'price_finish', 'service_id'], 'integer'],
            [['name', 'url', 'address'], 'safe'],
            [['latitude', 'longitude'], 'number'],
//            [['has_payment', 'is_open']],
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
        $query = Division::find()->enabled();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->price_start !== null) {
            $query->joinWith(['divisionServices']);
            $query->andWhere("{{%division_services}}.price >= :price_start", [":price_start" => $this->price_start]);
        }

        if ($this->price_finish !== null) {
            $query->joinWith(['divisionServices']);
            $query->andWhere("{{%division_services}}.price <= :price_finish", [":price_finish" => $this->price_finish]);
        }

        if ($this->service_id !== null) {
            $query->andWhere(['{{%services}}.id' => $this->service_id]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->division_id,
            'company_id' => $this->company_id,
            'city_id' => $this->city_id,

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

    public function formName()
    {
        return '';
    }
}
