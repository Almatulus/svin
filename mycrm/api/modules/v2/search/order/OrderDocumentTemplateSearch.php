<?php

namespace api\modules\v2\search\order;

use core\models\order\OrderDocumentTemplate;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CompanyDocumentSearch represents the model behind the search form about `core\models\company\CompanyDocument`.
 */
class OrderDocumentTemplateSearch extends Model
{
    public $name;
    public $category_id;
    public $company_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            ['category_id', 'integer'],
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
        $query = OrderDocumentTemplate::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new \DomainException(reset($errors)[0]);
        }

        $query->andFilterWhere(['category_id' => $this->category_id]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        if (!empty($this->company_id)) {
            $query->andWhere([
              'OR',
              ['company_id' => null],
              ['company_id' => $this->company_id]
            ]);
        }

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
