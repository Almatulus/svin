<?php

namespace api\modules\v2\search\company;

use core\models\ServiceCategory;
use yii\base\Model;
use yii\data\ActiveDataProvider;


class CategorySearch extends Model
{
    public $name;

    private $division_category_ids;
    private $company_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
        $query = ServiceCategory::find()
            ->enabled()
            ->andWhere([
                "OR",
                ['{{%service_categories}}.type' => ServiceCategory::TYPE_CATEGORY_STATIC],
                [
                    "AND",
                    ['{{%service_categories}}.type' => ServiceCategory::TYPE_CATEGORY_DYNAMIC],
                    ['{{%service_categories}}.company_id' => $this->company_id],
                ]
            ])->andWhere(['{{%service_categories}}.parent_category_id' => $this->division_category_ids]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $errors = $this->getErrors();
            throw new \InvalidArgumentException(reset($errors)[0]);
        }

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
    public function setCompanyId(int $company_id)
    {
        $this->company_id = $company_id;
    }

    /**
     * @param mixed $division_category_ids
     */
    public function setDivisionCategoryIds(array $division_category_ids)
    {
        $this->division_category_ids = $division_category_ids;
    }
}