<?php

namespace core\forms\webcall;

use core\models\customer\CompanyCustomer;
use core\models\webcall\WebCall;
use core\models\webcall\WebcallAccount;
use Exception;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * WebCallForm is the model behind the webcall api calls.
 *
 * @property string $from_date
 * @property integer $page
 * @property integer $items_on_page
 * @property string $to_date
 * @property string $action
 *
 * @property WebCall|null $_webcall
 */
class WebCallForm extends Model
{
    public $account_id;
    public $customer_id;
    public $from_date;
    public $to_date;
    public $type;

    public $action = 'calls.list';
    public $page = 1;
    public $items_on_page = 100;

    private $_webcall = null;
    private $_result = null;

    private $_unknownPhones;

    public function __construct(WebCall $model, array $config = [])
    {
        $this->_webcall = $model;
        parent::__construct($config);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['from_date', 'to_date', 'action'], 'required'],
            [['from_date', 'to_date'], 'safe'],
            ['action', 'string'],
            ['action', 'in', 'range' => array_keys($this->getActions())],
            ['items_on_page', 'integer', 'min' => 1, 'max' => 100],
            ['page', 'integer', 'min' => 1],

            ['account_id', 'integer'],
            ['customer_id', 'integer'],

            ['type', 'in', 'range' => [-1, 0, 1, 2]]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id'    => Yii::t('app', 'Account'),
            'customer_id'   => Yii::t('app', 'Customer'),
            'from_date'     => Yii::t('app', 'Start date'),
            'to_date'       => Yii::t('app', 'Finish date'),
            'page'          => Yii::t('app', 'Page ID'),
            'items_on_page' => Yii::t('app', 'Items on page'),
            'action'        => Yii::t('app', 'Action'),
            'type'          => Yii::t('app', 'Type')
        ];
    }

    /**
     * Sends request and sets result attribute. Creates log if request success
     */
    public function getCallsList()
    {
        $this->_result = $this->runRequest('calls.list', $this->getRequestParams());

        /* @TODO Get response from db in later requests */
        //if (is_string($this->_result)) {
        //    $this->_webcall->createLog('calls.list', $this->_result);
        //}
    }

    /**
     * @TODO Get response from db in later requests
     * Set result last result
     */
    public function setLastCallsList()
    {
        $log = $this->_webcall->getLastLog('calls.list', (new \DateTime()));
        if ($log !== null) {
            $this->_result = $log->response;
        }
    }

    /**
     * Returns call action result
     * @return array
     */
    public function getResult()
    {
        $result = json_decode($this->_result);

        if (!is_null($this->type) && $this->type != -1) {
            $result->results = array_filter($result->results, function ($call) {
                $valid = $call->direction == $this->type;
                if ($this->type == 2) {
                    $valid = $valid & $call->answered == 1;
                }
                return $valid;
            });
        }

        return $result;
    }

    /**
     * Returns list of actions
     * @return array
     */
    public function getActions()
    {
        return [
            'calls.list' => Yii::t('app', 'List of calls'),
        ];
    }

    /**
     * Returns list of types
     * @return array
     */
    public function getTypes()
    {
        return [
            -1 => "Все звонки",
            0  => "Входящие",
            1  => "Исходящие",
            2  => "Пропущенные",
        ];
    }

    /**
     * Returns number of pages available in request
     * @return integer
     */
    public function getAvailablePages()
    {
        $pages = 1;
        $results = $this->getResult();
        if (!empty($results)) {
            $pages += $results->results_remains / $this->items_on_page;
        }
        return $pages;
    }

    /**
     * @return mixed
     */
    public function getTotalCount()
    {
        $results = $this->getResult();
        return $results->results_remains + $results->results_count;
    }

    /**
     * Returns actions config
     * @return array
     */
    private function getRequestParams()
    {
        $params = [
            'calls.list' => [
                "max_results" => $this->items_on_page,
                "from_date"   => (new \DateTime($this->from_date))->getTimestamp(),
                "to_date"     => (new \DateTime($this->to_date))->getTimestamp(),
                "from_offset" => ($this->page - 1) * $this->items_on_page,
                "supervised"  => 1
            ],
        ];

        if ($this->account_id && $this->validate('account_id')) {
            $webcallAccount = WebcallAccount::find()->company()->byId($this->account_id)->one();
            if ($webcallAccount) {
                $params[$this->action]['supervised'] = 0;
                $params[$this->action]['user_account'] = $webcallAccount->email;
            }
        }

        if ($this->customer_id && $this->validate('customer_id')) {
            $companyCustomer = CompanyCustomer::find()->company()->id($this->customer_id)->one();
            if ($companyCustomer && !empty($companyCustomer->customer->phone)) {
                $params[$this->action]['phone'] = $companyCustomer->customer->phone;
            }
        }

        return $params[$this->action];
    }

    /**
     * Send request
     * @param string $action
     * @param array $params
     * @return bool|string
     * @throws Exception
     */
    private function runRequest($action, $params = [])
    {
        if (!($curl = curl_init())) {
            throw new Exception('Curl does not work');
        }

        $params = json_encode(ArrayHelper::merge([
            'user_name' => $this->_webcall->username,
            'api_key'   => $this->_webcall->api_key,
            'action'    => $action,
            'app_name'  => Yii::$app->name,
        ], $params));

        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->_webcall->getApiDomain(),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function getIncomingCount()
    {
        $calls = isset($this->getResult()->results) ? $this->getResult()->results : [];

        return sizeof(array_filter($calls, function ($call) {
            return $call->direction == 0;
        }));
    }

    public function getOutgoingCount()
    {
        $calls = isset($this->getResult()->results) ? $this->getResult()->results : [];

        return sizeof(array_filter($calls, function ($call) {
            return $call->direction == 1;
        }));
    }

    public function getMissedCount()
    {
        $calls = isset($this->getResult()->results) ? $this->getResult()->results : [];

        return sizeof(array_filter($calls, function ($call) {
            return $call->direction == 0 && !$call->answered;
        }));
    }

    public function getIncomingDuration()
    {
        $calls = isset($this->getResult()->results) ? $this->getResult()->results : [];

        return array_reduce($calls, function ($res, $call) {
            if ($call->direction == 1) {
                return $res;
            }
            return $res + $call->duration;
        }, 0);
    }

    public function getOutgoingDuration()
    {
        $calls = isset($this->getResult()->results) ? $this->getResult()->results : [];

        return array_reduce($calls, function ($res, $call) {
            if ($call->direction == 0) {
                return $res;
            }
            return $res + $call->duration;
        }, 0);
    }

    /**
     * @return array
     */
    public function getFirstCalls()
    {
        $calls = isset($this->getResult()->results) ? $this->getResult()->results : [];

        $all = 0;
        $missed = 0;
        foreach ($calls as $call) {
            if ($call->direction == 0) {
                if (isset($this->_unknownPhones[$call->client_number])
                    || !CompanyCustomer::find()->company()->phone($call->client_number)->exists()) {
                    $this->_unknownPhones[$call->client_number] = true;

                    $all++;
                    if (!$call->answered) {
                        $missed++;
                    }
                    continue;
                }
            }
        }

        return [
            'all'    => $all,
            'missed' => $missed
        ];
    }

    public function fields()
    {
        $firstCalls = $this->getFirstCalls();

        return [
            'from_date',
            'to_date',
            'incoming'           => 'incomingCount',
            'outgoing'           => 'outgoingCount',
            'missed'             => 'missedCount',
            'incoming_duration'  => 'incomingDuration',
            'outgoing_duration'  => 'outgoingDuration',
            'first_calls'        => function () use ($firstCalls) {
                return $firstCalls['all'];
            },
            'missed_first_calls' => function () use ($firstCalls) {
                return $firstCalls['missed'];
            }
        ];
    }
}
