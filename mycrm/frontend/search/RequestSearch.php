<?php

namespace frontend\search;

use core\models\customer\Customer;
use core\models\customer\CustomerRequest;
use DateTime;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * @property integer $staff
 * @property integer $user
 *
 * @property string $from
 * @property string $to
 * @property integer $type
 * @property integer $status
 * @property string $phone
 * @property Customer $customer
 */
class RequestSearch extends CustomerRequest
{
    private $_from;
    private $_to;

    public $type;
    public $status;
    public $phone;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['from', 'to', 'phone'], 'string'],
            [['type', 'status'], 'integer'],
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
     * Form name is overridden in order to shorten the search url
     * @return string formName
     */
    public function formName() {
        return '';
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

    // GETTERS and SETTERS

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
     * @param bool $own_company_only
     *
     * @return ActiveDataProvider
     */
    public function search($params, $own_company_only = false)
    {
        $query = CustomerRequest::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_time'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->join('LEFT JOIN','crm_customers','crm_customers.id = crm_customer_requests.customer_id');

        $query->andFilterWhere([
            'type' => $this->type,
            'status' => $this->status,
        ]);
        $query->andFilterWhere(['like', 'crm_customers.phone', $this->phone]);
        $query->andFilterWhere(['>=', 'crm_customer_requests.created_time', $this->from]);
        $query->andFilterWhere(['<=', 'crm_customer_requests.created_time', $this->to]);

        // Filter staff related to my company
        if ($own_company_only && !Yii::$app->user->isGuest)
        {
            $query->andWhere(['crm_customer_requests.company_id' => Yii::$app->user->identity->company_id]);
        }

        return $dataProvider;
    }
}
