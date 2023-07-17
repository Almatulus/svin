<?php

namespace core\models\warehouse;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class UsageHistorySearch
 * @package core\models\warehouse
 * @property Product $product
 */
class UsageHistorySearch extends Model
{
    public $product_id;
    public $start;
    public $end;

    protected $_product;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['product_id', 'default', 'value' => null],
            ['product_id', 'integer'],

            [['start', 'end'], 'date', 'format' => 'php:Y-m-d']
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = \core\models\warehouse\UsageProduct::find()
            ->joinWith([
                'use' => function (\core\models\warehouse\query\UsageQuery $query) {
                    return $query->company()->enabled()
                        ->andFilterWhere(['>=', '{{%warehouse_usage}}.created_at', $this->start])
                        ->andFilterWhere([
                            '<=',
                            '{{%warehouse_usage}}.created_at',
                            $this->end ? ($this->end . " 24:00:00") : null
                        ]);
                }
            ], false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate() || is_null($this->product_id)) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['{{%warehouse_usage_product}}.product_id' => $this->product_id]);

        return $dataProvider;
    }

    /**
     * @return null|Product
     */
    public function getProduct()
    {
        if (!$this->_product && $this->product_id) {
            $this->_product = Product::findOne($this->product_id);
        }
        return $this->_product;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }
}