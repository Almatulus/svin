<?php

namespace api\modules\v2\search\warehouse;

use core\models\warehouse\Category;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CategorySearch extends Model
{
    public $name;
    public $parent_id;
    private $company_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id'], 'integer'],
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
        $query = Category::find()->company($this->company_id);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $errors = $this->getErrors();
            throw new \InvalidArgumentException(reset($errors)[0]);
        }

        $query->andFilterWhere(['parent_id' => $this->parent_id]);
        $query->andFilterWhere(['ILIKE', 'name', $this->name]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }

    /**
     * @param mixed $company_id
     */
    public function setCompanyId($company_id)
    {
        $this->company_id = $company_id;
    }
}