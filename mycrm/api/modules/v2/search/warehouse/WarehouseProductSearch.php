<?php

namespace api\modules\v2\search\warehouse;

use core\models\warehouse\Product;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * WarehouseProductSearch represents the model behind the search form about `core\models\warehouse\Product`.
 */
class WarehouseProductSearch extends Model
{
    public $name;
    public $division_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['division_id', 'integer'],
            [['name'], 'safe'],
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
        $query = Product::find()->active()->permitted();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new \InvalidArgumentException(reset($errors)[0]);
        }

        $query->andFilterWhere(['division_id' => $this->division_id]);
        $query->andFilterWhere(['like', 'lower(name)', strtolower($this->name)]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
