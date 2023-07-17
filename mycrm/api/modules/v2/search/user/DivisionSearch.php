<?php

namespace api\modules\v2\search\user;

use core\models\division\Division;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class DivisionSearch extends Division
{
    public $service_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'city_id'], 'integer'],
            [['name', 'address'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return DivisionSearch|ActiveDataProvider
     */
    public function search($params)
    {
        $query = Division::find()->enabled()->permitted();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
            'sort'       => ['defaultOrder' => ['name' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            return $this;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            '{{%divisions}}.company_id' => $this->company_id,
            '{{%divisions}}.city_id'    => $this->city_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
              ->andFilterWhere(['like', 'address', $this->address]);

        return $dataProvider;
    }
}
