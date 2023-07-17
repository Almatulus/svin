<?php

namespace api\modules\v2\search\customer;

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
                ['company_id' => Yii::$app->user->identity->company_id],
                [
                    'AND',
                    [
                        'company_id' => null,
                        'type'       => CustomerSource::TYPE_DEFAULT
                    ]
                ]
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => ['name' => SORT_ASC],
            ],
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['ilike', 'name', $this->name]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}
