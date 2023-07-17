<?php

namespace frontend\search;

use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use core\models\CompanyPaymentLog;

/**
 * CompanyPaymentLogSearch represents the model behind the search form about `core\models\CompanyPaymentLog`.
 */
class CompanyPaymentLogSearch extends CompanyPaymentLog
{
    private $_from;
    private $_to;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'value', 'currency'], 'integer'],
            [['code', 'created_time', 'confirmed_time', 'description', 'message', 'from', 'to'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'from' => Yii::t('app', 'From'),
            'to' => Yii::t('app', 'To'),
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

    public function setFrom($value) {
        $this->_from = $value;
    }

    public function setTo($value) {
        $this->_to = $value;
    }

    public function getFrom() {
        if(!$this->_from) {
            $this->_from = (new DateTime($this->to))->modify("-1 months")->format("Y-m-d");
        }
        return $this->_from;
    }

    public function getTo() {
        if(!$this->_to) {
            $this->_to = (new DateTime())->modify("+1 day")->format("Y-m-d");
        }
        return $this->_to;
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
        $query = CompanyPaymentLog::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_time' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'company_id' => $this->company_id,
            'value' => $this->value,
            'currency' => $this->currency,
            'created_time' => $this->created_time,
            'confirmed_time' => $this->confirmed_time,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'message', $this->message]);

        $query->andFilterWhere(['>=', 'crm_company_payment_log.created_time', $this->from]);
        $query->andFilterWhere(['<=', 'crm_company_payment_log.created_time', $this->to]);

        return $dataProvider;
    }

    public function formName()
    {
        return '';
    }
}