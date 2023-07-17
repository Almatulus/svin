<?php

namespace api\modules\v2\search\country;

use core\models\Country;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Country represents the model behind the search form about `core\models\Country`.
 */
class CountrySearch extends Model
{
    public $name;
    public $active;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'string'],
            ['active', 'boolean'],
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
        $query = Country::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['active' => $this->active]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
