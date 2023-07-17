<?php

namespace api\modules\v2\search\customer;

use core\models\customer\CustomerLoyalty;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CustomerLoyaltySearch extends Model
{
    public $event;
    public $amount;
    public $discount;
    public $rank;
    public $category_id;
    public $mode;
    public $company_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event', 'amount', 'discount', 'rank', 'category_id', 'mode', 'company_id'], 'integer'],
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
        $query = CustomerLoyalty::find()->company();

        $dataProvider = new ActiveDataProvider([
            'query' => CustomerLoyalty::find()->company()
                ->orderBy(['mode' => 'SORT_ASC', 'event' => 'SORT_ASC', 'amount' => 'SORT_DESC'])
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['event' => $this->event]);
        $query->andFilterWhere(['amount' => $this->amount]);
        $query->andFilterWhere(['discount' => $this->discount]);
        $query->andFilterWhere(['rank' => $this->rank]);
        $query->andFilterWhere(['category_id' => $this->category_id]);
        $query->andFilterWhere(['mode' => $this->mode]);
        $query->andFilterWhere(['company_id' => $this->company_id]);

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