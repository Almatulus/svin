<?php

namespace frontend\search;

use core\models\order\query\OrderQuery;
use core\models\StaffPayment;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StaffPaymentSearch represents the model behind the search form about `core\models\StaffPayment`.
 */
class StaffPaymentSearch extends StaffPayment
{
    public $order_date;

    public function init()
    {
        $this->end_date = date("Y-m-d");
        $this->start_date = date("Y-m-d",
            strtotime($this->end_date . " -6 days"));
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['staff_id'], 'integer'],
            [['start_date', 'end_date', 'created_at', 'order_date'], 'safe'],
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
        $query = StaffPayment::find();

        // add conditions that should always apply here
        $query->joinWith('staff.divisions', false)
            ->andWhere(['company_id' => Yii::$app->user->identity->company_id]);

        $divisions = \Yii::$app->user->identity->permittedDivisions;
        if ($divisions) {
            $query->andWhere(['{{%staff_division_map}}.division_id' => $divisions]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['>=', '{{%staff_payments}}.payment_date', $this->start_date]);

        if (!empty($this->end_date)) {
            $endDateTime = new \DateTime($this->end_date);
            $endDateTime->modify('+1 day');
            $query->andWhere(['<', '{{%staff_payments}}.payment_date', $endDateTime->format('Y-m-d')]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            '{{%staff_payments}}.staff_id' => $this->staff_id,
        ]);

        if ($this->order_date) {
            $query->joinWith([
                'services.orderService.order' => function (OrderQuery $query) {
                    return $query->startFrom(new \DateTime($this->order_date))
                        ->to((new \DateTime($this->order_date))->modify("+1 day"));
                }
            ], false);
        }

        return $dataProvider;
    }
}
