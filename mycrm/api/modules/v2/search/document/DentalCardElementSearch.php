<?php

namespace api\modules\v2\search\document;

use core\models\document\DentalCardElement;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DentalCardElementSearch represents the model behind the search form about `core\models\document\DentalCardElement`.
 */
class DentalCardElementSearch extends Model
{
    public $document_id;
    public $number;
    public $diagnosis_id;
    public $mobility;
    public $company_customer_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'document_id',
                    'number',
                    'diagnosis_id',
                    'mobility',
                    'company_customer_id'
                ],
                'integer'
            ]
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
        $query = DentalCardElement::find()->joinWith('document');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['document_id' => SORT_ASC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'document_id'                        => $this->document_id,
            'number'                             => $this->number,
            'diagnosis_id'                       => $this->diagnosis_id,
            'mobility'                           => $this->mobility,
            '{{%documents}}.company_customer_id' => $this->company_customer_id
        ]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
