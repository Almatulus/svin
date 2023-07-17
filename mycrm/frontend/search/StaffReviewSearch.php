<?php

namespace frontend\search;

use core\models\StaffReview;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StaffReviewSearch represents the model behind the search form about `core\models\StaffReview`.
 */
class StaffReviewSearch extends StaffReview
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'staff_id', 'value', 'status'], 'integer'],
            [['created_time', 'comment'], 'safe'],
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
     * @param bool $own_company_only
     *
     * @return ActiveDataProvider
     */
    public function search($params, $own_company_only = false)
    {
        $query = StaffReview::find()->permitted();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id'                                   => $this->id,
            'customer_id'                          => $this->customer_id,
            StaffReview::tableName() . '.staff_id' => $this->staff_id,
            'created_time'                         => $this->created_time,
            'value'                                => $this->value,
            'status'                               => $this->status,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        // Filter staff related to my company
        if ($own_company_only && !Yii::$app->user->isGuest) {
            $query->joinWith('staff.divisions');
            $query->andWhere(['{{%divisions}}.company_id' => Yii::$app->user->identity->company_id]);
        }

        return $dataProvider;
    }
}
