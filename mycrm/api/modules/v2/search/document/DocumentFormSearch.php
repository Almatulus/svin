<?php

namespace api\modules\v2\search\document;

use core\models\company\query\CompanyPositionQuery;
use core\models\document\DocumentForm;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DocumentFormSearch represents the model behind the search form about `core\models\document\DocumentForm`.
 */
class DocumentFormSearch extends DocumentForm
{
    public $ids = [];
    public $companyPositionIDs = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
            [['has_dental_card'], 'boolean'],
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
        $query = DocumentForm::find()->enabled();

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

        if ($this->ids) {
            $query->andWhere(['{{%document_forms}}.id' => $this->ids]);
        }

        if ($this->companyPositionIDs) {
            $query->joinWith(['companyPositions' => function(CompanyPositionQuery $query) { /** @see DocumentForm::getCompanyPositions() */
                $query->andWhere(['{{%company_positions}}.id' => $this->companyPositionIDs]);
            }]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id'              => $this->id,
            'has_dental_card' => $this->has_dental_card,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
