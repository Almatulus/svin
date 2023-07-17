<?php

namespace api\modules\v2\search\comment;

use core\models\medCard\MedCardComment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MedCardDiagnosisSearch represents the model behind the search form about `core\models\medCard\MedCardDiagnosis`.
 *
 * @property integer $diagnosis_id
 */
class MedCardCommentSearch extends MedCardComment
{
    public $diagnosis_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['diagnosis_id', 'category_id'], 'integer'],
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
        $query = MedCardComment::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['comment' => SORT_DESC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->joinWith('diagnoses');
        $query->andFilterWhere(['{{%med_card_comments}}.category_id' => $this->category_id]);

        if (empty($this->diagnosis_id)) {
            $query->andWhere(['{{%med_card_diagnoses}}.id' => null]);
        } else {
            $query->andWhere(['{{%med_card_diagnoses}}.id' => $this->diagnosis_id]);
        }

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
