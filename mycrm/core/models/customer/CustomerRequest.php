<?php

namespace core\models\customer;

use common\components\events\CustomerRequestEventHandler;
use core\helpers\CompanyHelper;
use core\helpers\customer\CustomerHelper;
use core\helpers\customer\RequestTemplateHelper;
use core\jobs\customer\SendCustomerRequestSmsJob;
use core\jobs\customer\SendCustomerRequestWAJob;
use core\models\company\Company;
use core\models\order\Order;
use core\models\Staff;
use DateTime;
use Yii;

/**
 * This is the model class for table "{{%customer_requests}}".
 *
 * @property integer $id
 * @property integer $type
 * @property string $code
 * @property string $created_time
 * @property integer $customer_id
 * @property integer $receiver_phone
 * @property integer $status
 * @property integer $company_id
 * @property integer $smsc_id
 * @property integer $smsc_count
 * @property double $smsc_cost
 * @property string $smsc_error_code
 * @property string $smsc_status
 *
 * @property Customer $customer
 * @property Company $company
 */
class CustomerRequest extends \yii\db\ActiveRecord
{
    const CODE_SIZE = 4;

    const TYPE_REGISTER = 1;
    const TYPE_NOTIFICATION = 2;
    const TYPE_MESSAGE = 3;

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_USED = 2;

    public $smscStatus;
    public $smsCount;
    public $price;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_requests}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'customer_id', 'receiver_phone'], 'required'],
            [['type', 'customer_id', 'status', 'company_id'], 'integer'],
            [['code', 'smsc_count', 'smsc_id', 'smsc_cost'], 'safe'],
            [['receiver_phone'], 'string', 'max' => 255],
            ['receiver_phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'code' => Yii::t('app', 'Message'),
            'created_time' => Yii::t('app', 'Created Time'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'status' => Yii::t('app', 'Status'),
            'company_id' => Yii::t('app', 'Company ID'),
            'receiver_phone' => Yii::t('app', 'Receiver Phone'),
            'smscStatus' => Yii::t('app', 'Status'),
            'smsCount' => Yii::t('app', 'SMS'),
            'price' => Yii::t('app', 'Price'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * Returns customer request
     * @param string $number
     * @param string $code
     * @return CustomerRequest | null
     */
    public static function getCustomerRequest($number, $code)
    {
        return CustomerRequest::find()->joinWith(['customer'])->where([
            'crm_customers.phone' => $number,
            'code' => $code,
            'status' => CustomerRequest::STATUS_ENABLED
        ])->one();
    }

    /**
     * Set request used
     * @return mixed
     */
    public function setUsed()
    {
        $this->status = self::STATUS_USED;
        return $this->save();
    }

    /**
     * @TODO remove method from this class
     * Send SMS
     * @param string $to Phone number
     * @param string $message String with code
     * @param integer $company_id ID of company
     */
    public static function sendNotAssignedSMS($to, $message, $company_id = null)
    {
        $message = str_replace("'", '', $message);

        if ($company_id !== null) {
            $company = Company::findOne($company_id);
            if (!($company && $company->isActive())) {
                return;
            }
        }

        shell_exec("(php " . Yii::$app->basePath . "/../yii sms/send '{$to}' '{$message}') > /dev/null 2>/dev/null &");
    }

    public function sendSMS()
    {
        \Yii::$app->queue->push(new SendCustomerRequestSmsJob(['requestId' => $this->id]));
    }


    public function sendWA()
    {
        \Yii::$app->queue->push(new SendCustomerRequestWAJob(['requestId' => $this->id]));
    }

    /**
     * @return array
     */
    public static function getTypeLabels()
    {
        return [
            self::TYPE_REGISTER => Yii::t('app', 'Request type Register'),
            self::TYPE_NOTIFICATION => Yii::t('app', 'Request type Notification'),
            self::TYPE_MESSAGE => Yii::t('app', 'Request type Message'),
        ];
    }

    /**
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            self::STATUS_ENABLED => Yii::t('app', 'Request status Enabled'),
            self::STATUS_DISABLED => Yii::t('app', 'Request status Disabled'),
            self::STATUS_USED => Yii::t('app', 'Request status Used'),
        ];
    }

    public function getSmscStatus()
    {
        if($this->smsc_status !== null){
            return Yii::$app->sms->getStatusName($this->smsc_status);
        }

        if($this->smsc_error_code !== null){
            return Yii::$app->sms->getErrorName($this->smsc_error_code);
        }

        //@TODO remove this code, after checking SMSC callback
        if($this->smsc_id !== null){
            $jsonResult = Yii::$app->sms->status($this->smsc_id, $this->receiver_phone);
            $result = json_decode($jsonResult,true);

            $status = isset($result['status']) ? Yii::$app->sms->getStatusName($result['status']) . " "
                                               : Yii::$app->sms->getErrorName($result['error_code']);

            $status .= $result['last_date'] ?? "";

            return $status;
        }

        return null;
    }

    public function getSmsCount()
    {
        return $this->smsc_count ?? CompanyHelper::estimateNumberOfSms($this->code);
    }

    public function getPrice()
    {
        return isset($this->smsc_error_code) ? 0 : $this->getSmsCount() * Yii::$app->params['sms_cost'];
    }

    /**
     * @param $company_id
     * @param $key
     * @param $params
     * @return mixed|null|string
     */
    public static function generateMessage($company_id, $key, $params)
    {
        $template = CustomerRequestTemplate::loadTemplate($key, $company_id);

        if (!($template && $template->isEnabled())) {
            return null;
        }

        $result = $template->template;
        foreach ($params as $key => $value) {
            $result = str_replace('%' . $key . '%', $value, $result);
        }

        return $result;
    }

    /**
     * Sends template message to client
     *
     * @param Company  $company
     * @param string   $description
     * @param integer  $template message template id
     * @param array    $params   template params
     * @param string   $to       phone number
     * @param Customer $customer
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    private static function sendRequest(Company $company, $description, $template, $params, $to, Customer $customer)
    {
        $message = self::generateMessage($company->id, $template, $params);
        $paymentAmount = $company->unlimited_sms ? 0 : CompanyHelper::estimateSmsPrice(strval($message));
        if ($message && $company->canSendSms($message)) {
            $request = new CustomerRequest();
            $request->type = CustomerRequest::TYPE_NOTIFICATION;
            $request->status = CustomerRequest::STATUS_USED;
            $request->customer_id = $customer->id;
            $request->code = $message;
            $request->company_id = $company->id;
            $request->receiver_phone = $to;
            if ($request->save()) {
                if ($company->messaging_type === Company::MESSAGING_SMS) {
                    $request->sendSMS();
                } else {
                    $request->sendWA();
                }
                $company->createPayment($paymentAmount, $description, $request->id);
                return true;
            }
        }

        return false;
    }

    /**
     * Sends template message
     *
     * @param CustomerRequestEventHandler $event
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function sendOrderTemplateRequest(CustomerRequestEventHandler $event)
    {
        /* @var CompanyCustomer $companyCustomer */
        /* @var Order $order */
        /* @var Staff $staff */
        $order = $event->order;
        $companyCustomer = $order->companyCustomer;
        $customer = $companyCustomer->customer;
        $company = $companyCustomer->company;
        $staff = $event->order->staff;
        $division = $order->division;

        $description = \Yii::t('app', 'Message sent to {phone} with order notification', ['phone' => $customer->phone]);
        $datetime = new DateTime($event->order->datetime);

        return CustomerRequest::sendRequest(
            $company,
            $description,
            $event->template,
            [
                RequestTemplateHelper::HOURMINUTES => Yii::$app->formatter->asTime($datetime),
                RequestTemplateHelper::DATE => Yii::$app->formatter->asDate($datetime, "php:d.m.Y"),
                RequestTemplateHelper::LINK => Yii::$app->params['crm_host'],
                RequestTemplateHelper::CLIENT_NAME => $customer->name,
                RequestTemplateHelper::CLIENT_PHONE => $customer->phone,
                RequestTemplateHelper::DISCOUNT => null,
                RequestTemplateHelper::COMPANY_NAME => $company->name,
                RequestTemplateHelper::SERVICE_TITLE => $order->getServicesTitle(),
                RequestTemplateHelper::MASTER_NAME => $staff->getFullName(),
                RequestTemplateHelper::DATETIME => Yii::$app->formatter->asDatetime($datetime, "php:d.m.Y H:i"),
                RequestTemplateHelper::COMPANY_ADDRESS => $division->address,
                RequestTemplateHelper::CONTACT_PHONE => $staff->phone,
                RequestTemplateHelper::ORDER_KEY => $order->number,
                RequestTemplateHelper::DIVISION_NAME => $division->name,
                RequestTemplateHelper::DIVISION_ADDRESS => $division->address,
            ],
            $event->receiver,
            $customer
        );
    }

    /**
     * Sends template message not related to order
     *
     * @param CompanyCustomer $receiver
     * @param integer         $template
     * @param array           $data
     *
     * @return boolean
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function sendTemplateRequest(CompanyCustomer $receiver, $template, $data = [])
    {
        $company = $receiver->company;
        $customer = $receiver->customer;
        $description = \Yii::t('app', 'Message sent to {phone} with {template}', [
            'phone' => $customer->phone,
            'template' => CustomerRequestTemplate::getTemplateName($template)
        ]);


        $datetime = new DateTime();
        $template_data = array_merge([
            RequestTemplateHelper::HOURMINUTES => Yii::$app->formatter->asTime($datetime),
            RequestTemplateHelper::DATE => Yii::$app->formatter->asDate($datetime, "php:d.m.Y"),
            RequestTemplateHelper::LINK => Yii::$app->params['crm_host'],
            RequestTemplateHelper::CLIENT_NAME => $customer->name,
            RequestTemplateHelper::CLIENT_PHONE => $customer->phone,
            RequestTemplateHelper::DISCOUNT => $receiver->discount,
            RequestTemplateHelper::COMPANY_NAME => $receiver->company->name,
            RequestTemplateHelper::DATETIME => Yii::$app->formatter->asDatetime($datetime, "php:d.m.Y H:i"),
        ], $data);

        return CustomerRequest::sendRequest(
            $company,
            $description,
            $template,
            $template_data,
            $customer->phone,
            $customer
        );
    }

    /**
     * Sends custom message to client (using $company data/balance)
     *
     * @param CompanyCustomer $companyCustomer
     * @param Company         $company
     * @param                 $message
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function sendCustomRequest(CompanyCustomer $companyCustomer, Company $company, $message)
    {
        $description = \Yii::t('app', 'Custom message sent to {phone}', ['phone' => $companyCustomer->customer->phone]);
        $paymentAmount = $company->unlimited_sms ? 0 : CompanyHelper::estimateSmsPrice(strval($message));
        if ($company->canSendSms($message)) {
            $request = new CustomerRequest();
            $request->type = CustomerRequest::TYPE_MESSAGE;
            $request->status = CustomerRequest::STATUS_ENABLED;
            $request->customer_id = $companyCustomer->customer_id;
            $request->code = $message;
            $request->company_id = $company->id;
            $request->receiver_phone = $companyCustomer->customer->phone;

            if ($request->save()) {
                if ($company->messaging_type === Company::MESSAGING_SMS) {
                    $request->sendSMS();
                } else {
                    $request->sendWA();
                }
                $company->createPayment($paymentAmount, $description, $request->id);    
                return true;
            }
        }

        return false;
    }

    public function updateSmscInfo($smscId, $smscCost, $smscCount, $smscErrorCode, $smscStatus)
    {
        $this->smsc_id = $smscId;
        $this->smsc_cost = $smscCost;
        $this->smsc_count = $smscCount;
        $this->smsc_error_code = $smscErrorCode;
        $this->smsc_status = $smscStatus;
    }

}
