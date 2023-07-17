<?php

namespace frontend\modules\admin\search;

use core\models\company\Company;
use core\models\company\TariffPayment;
use core\models\company\Task;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * CompanySearch represents the model behind the search form about `core\models\company\Company`.
 */
class CompanySearch extends Company
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'logo_id', 'status', 'publish', 'category_id'], 'integer'],
            [['name', 'head_name'], 'safe'],
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
        $lastTariffsQuery = $this->getLastTariffsQuery();
        $lastTaskQuery = $this->getLastTaskQuery();

        $next_payment_date = new Expression("last_tariffs.start_date + (period::text||' month')::INTERVAL AS next_payment_date");

        $query = Company::find()
            ->leftJoin(['last_tariffs' => $lastTariffsQuery], '{{%companies}}.id = last_tariffs.company_id')
            ->leftJoin(['last_tasks' => $lastTaskQuery], '{{%companies}}.id = last_tasks.company_id')
            ->select([
                '{{%companies}}.*',
                $next_payment_date,
                'due_date'
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes'   => [
                    'id',
                    'name',
                    'head_name',
                    'category_id',
                    'tariff_id',
                    'lastTask.due_date' => [
                        'asc'  => new Expression('due_date ASC NULLS LAST'),
                        'desc' => new Expression('due_date DESC NULLS LAST')
                    ]
                ],
                'defaultOrder' => ['lastTask.due_date' => SORT_ASC]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'logo_id' => $this->logo_id,
            'status' => $this->status,
            'publish' => $this->publish,
            'category_id' => $this->category_id,
        ]);

        $query->andFilterWhere(['ilike', 'name', $this->name])
            ->andFilterWhere(['like', 'head_name', $this->head_name]);

        $dataProvider->sort->attributes['lastTariffPayment.nextPaymentDate'] = [
            'asc'  => ['next_payment_date' => SORT_ASC],
            'desc' => ['next_payment_date' => SORT_DESC]
        ];

        return $dataProvider;
    }

    private function getLastTariffsQuery()
    {
        $lastTariffsSubQuery = TariffPayment::find()
            ->select(['company_id', 'MAX(start_date) AS max_start_date'])
            ->groupBy('company_id');

        $lastTariffsQuery = TariffPayment::find()
            ->leftJoin(['sub' => $lastTariffsSubQuery], 'sub.company_id = {{%company_tariff_payments}}.company_id')
            ->andWhere('start_date = max_start_date');

        return $lastTariffsQuery;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    private function getLastTaskQuery()
    {
        $lastTaskSubQuery = Task::find()
            ->select(['company_id', 'MIN(due_date) AS min_due_date'])
            ->andWhere(['end_date' => null])
            ->groupBy('company_id');

        $lastTaskQuery = Task::find()
            ->leftJoin(['sub' => $lastTaskSubQuery], 'sub.company_id = {{%company_tasks}}.company_id')
            ->andWhere('due_date = min_due_date');

        return $lastTaskQuery;
    }


    public function formName()
    {
        return '';
    }
}
