<?php

namespace api\modules\v2\search\company;

use core\models\division\Division;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use core\models\company\CompanyPosition;

/**
 * CompanyPositionSearch represents the model behind the search form about `core\models\company\CompanyPosition`.
 */
class CompanyPositionSearch extends Model
{
    public $company_id;
    public $name;
    public $description;
    public $division_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id'], 'integer'],
            [['name', 'description'], 'safe'],
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
        $query = CompanyPosition::find()
            ->notDeleted()
            ->joinWith('staffs.divisions', false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new \InvalidArgumentException(reset($errors)[0]);
        }

        $query->andFilterWhere([
            CompanyPosition::tableName().'.company_id' => $this->company_id,
        ]);

        $query->andFilterWhere([
            'like',
            CompanyPosition::tableName().'.name',
            $this->name,
        ]);

        $query->andFilterWhere([
            'like',
            CompanyPosition::tableName().'.description',
            $this->description,
        ]);

        $query->andFilterWhere([
            Division::tableName().'.id' => $this->division_id
        ]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
