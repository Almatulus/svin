<?php

namespace api\modules\v2\search\customer;

use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerCategory;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CustomerCategorySearch extends Model
{
    public $name;
    public $color;
    public $discount;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'color'], 'string'],
            [['discount'], 'integer'],
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
        $query = CustomerCategory::find()->company();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'name' => SORT_ASC,
                ],
            ],
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['ilike', 'name', $this->name]);
        $query->andFilterWhere(['ilike', 'color', $this->color]);
        $query->andFilterWhere(['discount' => $this->discount]);

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