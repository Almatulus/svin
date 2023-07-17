<?php

namespace api\modules\v2\search\order;

use core\models\order\OrderDocument;
use core\models\order\OrderDocumentTemplate;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CompanyDocumentSearch represents the model behind the search form about `core\models\company\CompanyDocument`.
 */
class OrderDocumentSearch extends Model
{
    public $name;
    public $order_id;
    public $date;
    public $template_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['date', 'date'],
            [['order_id', 'template_id'], 'integer'],
            [['name'], 'string'],
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
        $query = OrderDocument::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['date' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new \DomainException(reset($errors)[0]);
        }

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['order_id' => $this->order_id]);
        $query->andFilterWhere(['template_id' => $this->template_id]);
        $query->andFilterWhere(['date' => $this->date]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
