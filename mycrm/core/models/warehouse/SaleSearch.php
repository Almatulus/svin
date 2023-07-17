<?php

namespace core\models\warehouse;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SaleSearch represents the model behind the search form about `core\models\warehouse\Sale`.
 *
 * @property string $name
 */
class SaleSearch extends Sale
{
    public $name;
    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cash_id', 'company_customer_id', 'discount', 'staff_id'], 'integer'],
            [['paid'], 'number'],
            [['sale_date', 'name'], 'safe'],
            [['from_date', 'to_date'], 'datetime', 'format' => 'php:Y-m-d'],
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
        $query = Sale::find()->distinct();

        // add conditions that should always apply here
        $query->joinWith(['saleProducts.product', 'division'])
            ->andFilterWhere(['{{%warehouse_sale}}.division_id' => Yii::$app->user->identity->permittedDivisions]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['sale_date' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->staff_id) {
            $query->andWhere(['{{%warehouse_sale}}.staff_id' => $this->staff_id]);
        }

        $query->andFilterWhere([
            'OR',
            ['~*', '{{%warehouse_product}}.name', $this->name],
            ['like', '{{%warehouse_product}}.barcode', $this->name],
            ['like', '{{%warehouse_product}}.sku', $this->name]
        ]);

        $query->andFilterWhere(['>=', '{{%warehouse_sale}}.sale_date', $this->from_date]);
        $query->andFilterWhere(['<=', '{{%warehouse_sale}}.sale_date', $this->to_date]);

        return $dataProvider;
    }
}
