<?php

namespace frontend\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use core\models\ServiceCategory;

/**
 * ServiceCategorySearch represents the model behind the search form about `core\models\ServiceCategory`.
 */
class ServiceCategorySearch extends ServiceCategory
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'image_id', 'parent_category_id'], 'integer'],
            [['name'], 'safe'],
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
     * @param boolean $needs_root
     *
     * @return ActiveDataProvider
     */
    public function search($params, $needs_root = true)
    {
        $query = ServiceCategory::find();

        if(!$needs_root)
        {
            $query->andWhere("parent_category_id IS NOT NULL");
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['order' => SORT_ASC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'image_id' => $this->image_id,
            'parent_category_id' => $this->parent_category_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
