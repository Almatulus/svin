<?php

namespace core\models\warehouse;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UsageSearch represents the model behind the search form about `core\models\warehouse\Usage`.
 */
class UsageSearch extends Usage
{
    public $name;
    public $start;
    public $end;
    public $staff_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'company_customer_id', 'staff_id'], 'integer'],
            [['name', 'created_at', 'updated_at'], 'safe'],
            [['start', 'end'], 'date', 'format' => 'php:Y-m-d']
        ];
    }

    public function formName()
    {
        return '';
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
        $query = Usage::find()->distinct()->company()->active()->permitted()->joinWith(['usageProducts.product.unit']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->staff_id) {
            $query->andWhere(['{{%warehouse_usage}}.staff_id' => $this->staff_id]);
        }

        $query->andFilterWhere(['~*', '{{%warehouse_product}}.name', $this->name])
            ->orFilterWhere(['like', '{{%warehouse_product}}.barcode', $this->name])
            ->orFilterWhere(['like', '{{%warehouse_product}}.sku', $this->name]);

        $query->andFilterWhere(['>=', '{{%warehouse_usage}}.created_at', $this->start]);
        $query->andFilterWhere([
            '<=',
            '{{%warehouse_usage}}.created_at',
            $this->end ? ($this->end . " 24:00:00") : null
        ]);

        return $dataProvider;
    }
}
