<?php

namespace api\modules\v2\search\company;

use core\models\medCard\MedCardComment;
use core\models\medCard\MedCardCompanyComment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MedCardCompanyCommentSearch represents the model behind the search form about `core\models\medCard\MedCardCompanyComment`.
 */
class MedCardCompanyCommentSearch extends Model
{
    public $company_id;
    public $category_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
        $query = MedCardCompanyComment::find();

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

        $query->andFilterWhere(['company_id' => $this->company_id]);
        $query->andFilterWhere(['category_id' => $this->category_id]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
