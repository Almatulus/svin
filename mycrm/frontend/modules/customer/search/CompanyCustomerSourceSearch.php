<?php

namespace frontend\modules\customer\search;

use core\models\customer\CompanyCustomer;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use core\models\customer\CustomerSource;

/**
 * CompanyCustomerSourceSearch represents the model behind the search form about `core\models\customer\CustomerSource`.
 */
class CompanyCustomerSourceSearch extends Model
{
    public $name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'safe'],
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
        $query = CustomerSource::find()
            ->andWhere([
                'OR',
                [
                    'company_id' => null,
                    'type'       => CustomerSource::TYPE_DEFAULT,
                ],
                [
                    'company_id' => Yii::$app->user->identity->company_id,
                    'type'       => CustomerSource::TYPE_DYNAMIC,
                ]
            ]);
        $subQuery = CompanyCustomer::find()
            ->select('source_id, COUNT(source_id) as company_customers_count')
            ->groupBy('source_id');
        $query->leftJoin(["companyCustomerSum" => $subQuery], '"companyCustomerSum".source_id = id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['type' => SORT_ASC],
                'attributes'   => [
                    'type',
                    'name',
                    'companyCustomersCount' => [
                        'asc' => ['companyCustomerSum.company_customers_count' => SORT_ASC],
                        'desc' => ['companyCustomerSum.company_customers_count' => SORT_DESC],
                    ]
                ],
            ],
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['ilike', 'name', $this->name]);

        return $dataProvider;
    }
}
