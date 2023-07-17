<?php

namespace core\models\warehouse;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DeliverySearch represents the model behind the search form about `core\models\warehouse\Delivery`.
 *
 * @property string $name
 */
class DeliverySearch extends Delivery
{
    public $name;
    public $from_date;
    public $to_date;
    public $created_from_date;
    public $created_to_date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'contractor_id', 'type'], 'integer'],
            [['delivery_date', 'invoice_number', 'name', 'notes', 'created_at', 'updated_at'], 'safe'],
            [['from_date', 'to_date', 'created_from_date', 'created_to_date'], 'datetime', 'format' => 'php:Y-m-d'],
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

    public function init()
    {

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
        $query = Delivery::find()->distinct()->company()->permitted()->enabled();

        // add conditions that should always apply here
        $query->joinWith('products.product');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'delivery_date' => SORT_DESC,
                    'created_at'    => SORT_ASC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['~*', 'name', $this->name])
            ->orFilterWhere(['like', 'barcode', $this->name])
            ->orFilterWhere(['like', 'sku', $this->name]);

        $query->andFilterWhere(['contractor_id' => $this->contractor_id]);

        $query->andFilterWhere(['>=', 'delivery_date', $this->from_date]);
        $query->andFilterWhere(['<=', 'delivery_date', $this->to_date]);
        $query->andFilterWhere(['>=', 'created_at', $this->created_from_date]);
        $query->andFilterWhere(['<=', 'created_at', $this->created_to_date]);

        return $dataProvider;
    }
}
