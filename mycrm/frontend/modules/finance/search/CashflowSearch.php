<?php

namespace frontend\modules\finance\search;

use core\models\customer\CompanyCustomer;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\finance\query\CashflowQuery;
use core\models\warehouse\Product;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CashflowSearch represents the model behind the search form about `core\models\CompanyCashflow`.
 * @property Product $product
 */
class CashflowSearch extends Model
{
    public $created_from;
    public $created_to;

    public $order_from;
    public $order_to;

    public $sCost;
    public $sCostType;
    public $sContractor;
    public $sCash;
    public $sStaff;
    public $sCustomer;
    public $sStatus;
    public $sDivision;
    public $sProductId;
    public $sOrder;
    public $sDivisionService;
    public $isOrder;
    public $comment;

    private $_product;

    /**
     * Form name is overridden in order to shorten the search url
     * @return string formName
     */
    public function formName() {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'created_from' => Yii::t('app', 'Дата создания'),
            'created_to' => 'до',
            'order_from' => Yii::t('app', 'Дата записи'),
            'order_to' => 'до',
            'sCustomer' => Yii::t('app', 'Customer'),
            'sCash' => Yii::t('app', 'Cash'),
            'sOrder' => Yii::t('app', 'Order Number'),
            'sCost' => Yii::t('app', 'CostItems'),
            'sProductId' => Yii::t('app', 'Product'),
            'sStaff' => Yii::t('app', 'Staff'),
            'sContractor' => Yii::t('app', 'Contractor'),
            'sDivision' => Yii::t('app', 'Division ID')
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sCostType', 'sContractor', 'sCash', 'sDivision', 'sStaff', 'sCustomer', 'sStatus', 'sProductId', 'sOrder', 'sDivisionService'], 'integer'],
            ['sCost', 'each', 'rule' => ['integer']],
            ['isOrder', 'boolean'],
            ['isOrder', 'default', 'value' => 0],
            [['created_from', 'created_to', 'order_from', 'order_to'], 'date', 'format' => 'php:Y-m-d'],
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
        $query = CompanyCashflow::find()
            ->active()
            ->company(\Yii::$app->user->identity->company_id)
            ->permittedDivisions();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'attributes' => [
                    'order_id' => [
                        'asc'  => [
                            'order_id'   => SORT_ASC,
                            'created_at' => SORT_ASC,
                        ],
                        'desc' => [
                            'order_id'   => SORT_DESC,
                            'created_at' => SORT_ASC,
                        ],
                    ],
                    'created_at'
                ],
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        if (!empty($this->sCost)) {
            $show_sum = !empty(array_intersect([-1, -2], $this->sCost));
            if ($show_sum) {
                $query->joinWith(['costItem']);
                $cost_item = [];
                in_array(-1, $this->sCost) ? array_push($cost_item, CompanyCostItem::TYPE_INCOME) : null;
                in_array(-2, $this->sCost) ? array_push($cost_item, CompanyCostItem::TYPE_EXPENSE) : null;
                $query->andFilterWhere(['{{%company_cost_items}}.type' => $cost_item]);
            } else {
                $query->andFilterWhere(['cost_item_id' => $this->sCost]);
            }
        }

        if ($this->isOrder) {
            $query->innerJoinWith(['order']);
        }

        if(!empty($this->sProductId)){
            $productId = $this->sProductId;
            $query->joinWith('products pr')->andWhere(['pr.product_id' => $productId]);
        }

        if (!empty($this->created_from)) {
            $query->andFilterWhere(['>=', 'created_at', strtotime(date('Y-m-d', strtotime($this->created_from)))]);
        }
        if (!empty($this->created_to)) {
            $query->andFilterWhere(['<', 'created_at', strtotime(date('Y-m-d', strtotime("{$this->created_to} + 1 day")))]);
        }

        $query->andFilterWhere(['>=', 'date', $this->order_from]);
        $query->andFilterWhere(['<', 'date', $this->order_to]);
        $query->cash($this->sCash);
        $query->andFilterWhere(['=', 'contractor_id', $this->sContractor]);
        $query->andFilterWhere(['=', 'customer_id', $this->sCustomer]);
        $query->andFilterWhere(['=', '{{%company_cashflows}}.staff_id', $this->sStaff]);
        $query->andFilterWhere(['=', '{{%company_cashflows}}.division_id', $this->sDivision]);
        $query->andFilterWhere(['like', 'comment', $this->comment]);

        if (!empty($this->sOrder)) {
            $query->joinWith(['order ord']);

            $query->andWhere([
                'OR',
                ['ord.number' => $this->sOrder]
            ]);
        }

        if( ! empty($this->sDivisionService)){
            $query->joinWith(['services so'])->andWhere(['so.service_id' => $this->sDivisionService]);
        }

        return $dataProvider;
    }

    /**
     * @param CashflowQuery $query
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getTotalValue(CashflowQuery $query)
    {
        $income = (clone($query))->income()->sum('value');
        $expense = (clone($query))->expense()->sum('value');
        return Yii::$app->formatter->asDecimal($income - $expense);
    }

    public function getProduct()
    {
        if(!$this->_product){
            $this->_product = empty($this->sProductId) ? null : Product::findOne($this->sProductId);
        }
        return $this->_product;
    }
}
