<?php

namespace api\modules\v2\search\country;

use core\models\City;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Country represents the model behind the search form about `core\models\City`.
 */
class CitySearch extends Model
{
    public $country_id;
    public $name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['country_id'], 'integer'],
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
        $query = City::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['country_id' => $this->country_id]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
