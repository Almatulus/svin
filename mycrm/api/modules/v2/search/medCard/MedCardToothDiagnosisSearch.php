<?php

namespace api\modules\v2\search\medCard;

use core\models\document\Document;
use core\models\medCard\MedCardToothDiagnosis;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DocumentSearch represents the model behind the search form about `core\models\document\DocumentForm`.
 */
class MedCardToothDiagnosisSearch extends Model
{
    public $name;
    public $abbreviation;
    public $color;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'abbreviation', 'color'], 'string'],
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
        $query = MedCardToothDiagnosis::find()
            ->andWhere(['company_id' => \Yii::$app->user->identity->company_id]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 1000,
            ],
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['ilike', 'name', $this->name]);
        $query->andFilterWhere(['ilike', 'abbreviation', $this->abbreviation]);
        $query->andFilterWhere(['ilike', 'color', $this->color]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
