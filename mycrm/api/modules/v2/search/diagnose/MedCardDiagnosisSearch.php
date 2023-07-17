<?php

namespace api\modules\v2\search\diagnose;

use core\models\medCard\MedCardDiagnosis;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MedCardDiagnosisSearch represents the model behind the search form about `core\models\medCard\MedCardDiagnosis`.
 *
 * @property string $q
 */
class MedCardDiagnosisSearch extends MedCardDiagnosis
{
    public $q;
    public $service_category_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['service_category_id', 'integer'],
            [['name', 'code', 'q'], 'safe'],
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
        $query = MedCardDiagnosis::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['ILIKE', '{{%med_card_diagnoses}}.name', $this->name])
            ->andFilterWhere(['ILIKE', '{{%med_card_diagnoses}}.code', $this->code]);

        $query->andFilterWhere(['ILIKE', '{{%med_card_diagnoses}}.name', $this->q])
            ->orFilterWhere(['ILIKE', '{{%med_card_diagnoses}}.code', $this->q]);

        if ( ! empty($this->service_category_id)) {
            $query->joinWith('serviceCategories');
            $query->andWhere(['{{%service_categories}}.id' => intval($this->service_category_id)]);
        }

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
