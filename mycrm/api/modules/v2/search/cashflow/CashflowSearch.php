<?php

namespace api\modules\v2\search\cashflow;

use core\models\finance\CompanyCashflow;
use core\models\warehouse\Product;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * CashflowSearch represents the model behind the search form about `core\models\CompanyCashflow`.
 * @property Product $product
 */
class CashflowSearch extends CompanyCashflow
{
    public $from;
    public $to;
    public $costs;
    public $costType;
    public $contractor;
    public $cash;
    public $staff;
    public $sCustomer;
    public $sStatus;
    public $division;
    public $productId;
    public $order;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->to = date("Y-m-d");
        $this->from = date("Y-m-d", strtotime($this->to . " -6 days"));
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'from'       => Yii::t('app', 'From'),
            'to'         => Yii::t('app', 'To'),
            'cash'       => Yii::t('app', 'Cash'),
            'contractor' => Yii::t('app', 'Contractor'),
            'division'   => Yii::t('app', 'Division ID')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['costType', 'contractor', 'cash', 'division', 'staff', 'sCustomer', 'sStatus', 'productId', 'order'],
                'integer'
            ],
            [['from', 'to',], 'date', 'format' => 'yyyy-MM-dd'],
            [['costs'], 'each', 'rule' => ['integer']]
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = CompanyCashflow::find()
            ->active()
            ->company(\Yii::$app->user->identity->company_id)
            ->permittedDivisions();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['date' => SORT_DESC, 'id' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (!empty($this->productId)) {
            $productId = $this->productId;
            $query->joinWith([
                'cashflowProduct' => function (ActiveQuery $query) use (&$productId) {
                    $query->joinWith([
                        'orderProduct' => function (ActiveQuery $query) use (&$productId) {
                            $query->andFilterWhere(['=', '{{%order_service_products}}.product_id', $productId]);
                        }
                    ]);
                }
            ]);
        }

        $query->andFilterWhere([
            'cash_id'                            => $this->cash,
            'contractor'                         => $this->contractor,
            'customer_id'                        => $this->sCustomer,
            'staff_id'                           => $this->staff,
            'cost_item_id'                       => $this->costs,
            '{{%company_cashflows}}.division_id' => $this->division
        ]);
        $query->andFilterWhere(['>=', 'date', $this->from]);
        $query->andFilterWhere(['<', 'date', $this->to ? ($this->to . " 24:00:00") : null]);
        $query->andFilterWhere(['like', 'comment', $this->comment]);

        if (!empty($this->order)) {
            $query->joinWith(['cashflowService.orderService.order so', 'cashflowProduct.orderProduct.order po']);

            $query->andWhere([
                'OR',
                ['so.number' => $this->order],
                ['po.number' => $this->order]
            ]);
        }

        return $dataProvider;
    }

    /**
     * Form name is overridden in order to shorten the search url
     * @return string formName
     */
    public function formName()
    {
        return '';
    }
}
