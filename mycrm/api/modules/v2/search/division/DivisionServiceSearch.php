<?php

namespace api\modules\v2\search\division;

use core\models\division\DivisionService;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DivisionServiceSearch represents the model behind the search form about `core\models\DivisionService`.
 *
 * @property string  $name
 * @property integer $company_id
 * @property integer $division_id
 * @property integer $staff_id
 */
class DivisionServiceSearch extends DivisionService
{
    public $name;
    public $company_id;
    public $division_id;
    public $staff_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'division_id', 'staff_id'], 'integer'],
            ['name', 'string', 'max' => 255]
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
        $query = DivisionService::find()
            ->permitted()
            ->distinct()
            ->deleted(false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['service_name' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->joinWith(['divisions', 'staffs']);
        $query->andFilterWhere([
            '{{%divisions}}.id'         => $this->division_id,
            '{{%divisions}}.company_id' => $this->company_id
        ]);

        $query->andFilterWhere(['{{%staffs}}.id' => $this->staff_id]);
        $query->andFilterWhere(['~*', 'service_name', $this->name]);

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}
