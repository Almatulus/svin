<?php

namespace frontend\modules\division\search;

use core\models\division\Division;
use core\models\division\DivisionService;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DivisionServiceSearch represents the model behind the search form about `core\models\DivisionService`.
 */
class DivisionServiceSearch extends DivisionService
{
    public $division_id;
    public $name;
    public $deleted = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['division_id', 'integer'],
            ['name', 'string', 'max' => 255],
            ['insurance_company_id', 'integer'],
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
     * @param integer $category_id
     *
     * @return ActiveDataProvider
     */
    public function search($params, $category_id = null)
    {
        $query = DivisionService::find()->distinct()->permitted();

        $query->deleted($this->deleted);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            // 'id' => $this->id,
            '{{%divisions}}.id'    => $this->division_id,
            'insurance_company_id' => $this->insurance_company_id,
            // 'price' => $this->price,
            // 'average_time' => $this->average_time,
        ]);

        if ($category_id !== null) {
            $query->joinWith('categories', true)->andWhere([
                'OR',
                ['{{%service_categories}}.parent_category_id' => $category_id],
                ['{{%service_categories}}.id' => $category_id]
            ]);
        }

        if (!Yii::$app->user->isGuest) {
            $query->joinWith("divisions");
            $query->andWhere(['{{%divisions}}.status' => Division::STATUS_ENABLED]);
            $query->andWhere(["{{%divisions}}.company_id" => Yii::$app->user->identity->company_id]);
        }

        if ( ! empty($this->name)) {
            $query->andWhere(['~*', 'service_name', quotemeta($this->name)]);
        }

        return $dataProvider;
    }
}
