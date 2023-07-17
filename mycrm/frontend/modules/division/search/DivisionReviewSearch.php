<?php

namespace frontend\modules\division\search;

use core\models\division\DivisionReview;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DivisionReviewSearch represents the model behind the search form about `core\models\DivisionReview`.
 */
class DivisionReviewSearch extends DivisionReview
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'division_id', 'value', 'status'], 'integer'],
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
        $query = DivisionReview::find()->permitted();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'division_id' => $this->division_id,
            'created_time' => $this->created_time,
            'value' => $this->value,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        // Filter staff related to my company
        if ($own_company_only && !Yii::$app->user->isGuest)
        {
            $query->joinWith('division');
            $query->andWhere(['crm_divisions.company_id' => Yii::$app->user->identity->company_id]);
        }

        return $dataProvider;
    }
}
