<?php

namespace api\modules\v2\search\company;

use core\models\CompanyPaymentLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

/**
 * CompanyPaymentLogSearch represents the model behind the search form about `core\models\CompanyPaymentLog`.
 */
class CompanyPaymentLogSearch extends Model
{
    public $company_id;
    public $value;
    public $currency;
    public $code;
    public $description;
    public $message;
    public $confirmed_time;
    public $created_time;
    public $start;
    public $end;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value', 'currency'], 'integer'],
            [
                [
                    'code',
                    'created_time',
                    'confirmed_time',
                    'description',
                    'message',
                    'start',
                    'end'
                ],
                'safe'
            ],
            [
                'end',
                'filter',
                'filter' => function ($value) {
                    // increase end date by 1 day, to find records of end date inclusively
                    $date = \DateTime::createFromFormat('Y-m-d', $value);
                    if ($date !== false) {
                        return $date->modify("+1 day")->format("Y-m-d");
                    }
                    return $value;
                }
            ]
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     * @throws BadRequestHttpException
     */
    public function search($params)
    {
        $query = CompanyPaymentLog::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['created_time' => SORT_DESC]]
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            $errors = $this->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'company_id'     => $this->company_id,
            'value'          => $this->value,
            'currency'       => $this->currency,
            'created_time'   => $this->created_time,
            'confirmed_time' => $this->confirmed_time,
        ]);

        $query->andFilterWhere(['>=', 'created_time', $this->start]);
        $query->andFilterWhere(['<=', 'created_time', $this->end]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }
}