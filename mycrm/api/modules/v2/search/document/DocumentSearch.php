<?php

namespace api\modules\v2\search\document;

use core\models\document\Document;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DocumentSearch represents the model behind the search form about `core\models\document\DocumentForm`.
 */
class DocumentSearch extends Model
{
    public $document_form_id;
    public $customer_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_form_id', 'customer_id'], 'integer']
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
        $query = Document::find()->company();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['id' => SORT_ASC]
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
            'document_form_id'    => $this->document_form_id,
            'company_customer_id' => $this->customer_id,
        ]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
