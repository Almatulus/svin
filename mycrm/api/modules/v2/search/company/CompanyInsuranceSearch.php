<?php

namespace api\modules\v2\search\company;

use core\models\InsuranceCompany;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * InsuranceSearch represents the model behind the search form about `core\models\company\Insurance`.
 */
class CompanyInsuranceSearch extends Model
{
    public $name;
    public $is_enabled = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'safe'],
            [['is_enabled'], 'boolean'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return CompanyInsuranceSearch|ActiveDataProvider
     */
    public function search($params)
    {
        $query = InsuranceCompany::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['name' => SORT_ASC]],
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            return $this;
        }

        if ($this->is_enabled !== null) {
            if (boolval($this->is_enabled)) {
                $query->innerJoinWith('companyInsurances')
                    ->andWhere(['{{%company_insurances}}.company_id' => \Yii::$app->user->identity->company_id]);
            } else {
                $query->join(
                    'LEFT JOIN',
                    '{{%company_insurances}}',
                    '{{%company_insurances}}.insurance_company_id = {{%insurance_companies}}.id AND {{%company_insurances}}.company_id = :company_id',
                    [':company_id' => \Yii::$app->user->identity->company_id]
                )->andWhere(['{{%company_insurances}}.id' => null]);
            }
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
