<?php

namespace api\modules\v2\search\service;

use core\models\ServiceCategory;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ServiceCategory represents the model behind the search form about `core\models\ServiceCategory`.
 */
class ServiceCategorySearch extends Model
{
    public $name;
    public $parent_category_id;
    public $order;
    public $company_id;
    public $type;
    public $status;
    public $is_root;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [
                ['parent_category_id', 'order', 'company_id', 'type'],
                'integer'
            ],
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
        $query = ServiceCategory::find()->enabled();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        if ( ! empty($this->is_root)) {
            $query->root();
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
