<?php

namespace core\models\webcall;

use core\models\company\Company;
use Exception;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%company_webcalls}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $api_key
 * @property string $username
 * @property string $domain
 * @property boolean $enabled
 *
 * @property Company $company
 */
class WebCall extends \yii\db\ActiveRecord
{
    const CALL_IN = 0;
    const CALL_OUT = 1;

    /**
     * @param Company $company
     * @param string $api_key
     * @param string $username
     * @param string $domain
     * @param boolean $enabled
     * @return WebCall
     */
    public static function add(Company $company, $enabled, $api_key = null, $username = null, $domain = null)
    {
        $model = new WebCall();
        $model->populateRelation('company', $company);
        $model->enabled = $enabled;
        $model->api_key = $api_key;
        $model->username = $username;
        $model->domain = $domain;
        return $model;
    }

    /**
     * @param string $api_key
     * @param string $username
     * @param string $domain
     */
    public function edit($api_key, $username, $domain)
    {
        $this->api_key = $api_key;
        $this->username = $username;
        $this->domain = $domain;
    }

    /**
     * @param boolean $status
     */
    public function changeStatus($status)
    {
        $this->enabled = $status;
    }

    public function isEnabled(): bool
    {
        return boolval($this->enabled);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_webcalls}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'api_key' => Yii::t('app', 'Api Key'),
            'username' => Yii::t('app', 'Api username'),
            'enabled' => Yii::t('app', 'Enabled'),
            'domain' => Yii::t('app', 'Domain'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * Create new log data
     * @param string $action
     * @param string $response
     * @return bool
     */
    public function createLog($action, $response): bool
    {
        try {
            $model = new WebCallLog();
            $model->company_id = $this->company_id;
            $model->api_key = $this->api_key;
            $model->username = $this->username;
            $model->response = $response;
            $model->action = $action;
            return $model->save();
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Subscribe actions
     */
    public function subscribe()
    {
        if (!$this->company->hasWebCallAccess()) {
            throw new AccessDeniedException('Access denied');
        }

        if (!($curl = curl_init())) {
            throw new Exception('Curl does not work');
        }

        $params = json_encode([
            'user_name' => $this->username,
            'api_key' => $this->api_key,
            'action' => "webhook.subscribe",
            'hooks' => [
                "call.start" => Url::to(['/webcall/default/call-start', 'company_id' => $this->company_id], true),
            ],
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->getApiDomain(),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Subscribe actions
     */
    public function unsubscribe()
    {
        if (!($curl = curl_init())) {
            throw new Exception('Curl does not work');
        }

        $params = json_encode([
            'user_name' => $this->username,
            'api_key' => $this->api_key,
            'action' => "webhook.unsubscribe",
            'hooks' => [
                "call.start",
            ],
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->getApiDomain(),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }


    /**
     * Create employer actions
     * @param string $name
     * @param string $email
     * @param string $password
     * @return mixed
     * @throws Exception
     * @internal param string $password_confirm
     * @internal param $string $
     */
    public function createEmployer(string $name, string $email, string $password)
    {
        if (!$this->company->hasWebCallAccess()) {
            throw new AccessDeniedException('Access denied');
        }

        if (!($curl = curl_init())) {
            throw new Exception('Curl does not work');
        }

        $params = json_encode([
            'user_name'             => $this->username,
            'api_key'               => $this->api_key,
            'action'                => "company.create_employee",
            'employee_user_name'    => $email,
            'employee_password'     => $password,
            'employee_display_name' => $name,
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->getApiDomain(),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params
        ]);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * @param string $email
     * @return mixed
     * @throws Exception
     */
    public function deleteEmployer(string $email)
    {
        if (!$this->company->hasWebCallAccess()) {
            throw new AccessDeniedException('Access denied');
        }

        if (!($curl = curl_init())) {
            throw new Exception('Curl does not work');
        }

        $params = json_encode([
            'user_name'          => $this->username,
            'api_key'            => $this->api_key,
            'action'             => "company.delete_employee",
            'employee_user_name' => $email
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->getApiDomain(),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $params
        ]);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * Returns valid API url
     * @return string
     */
    public function getApiDomain()
    {
        return "https://" . $this->domain . "/api/v1";
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $related = $this->getRelatedRecords();
            /** @var Company $company */
            if (isset($related['company']) && $company = $related['company']) {
                $company->save();
                $this->company_id = $company->id;
            }
            return true;
        }
        return false;
    }

    /**
     * Returns last log for date
     * @param string $action
     * @param \DateTime $date
     * @return WebCallLog|null
     */
    public function getLastLogs($action, \DateTime $date)
    {
        return WebCallLog::find()->where([
            'action' => $action,
            'company_id' => $this->company_id
        ])->andWhere('created_time > :date', [
            ':date' => $date->format('Y-m-d 00:00:00')
        ])->orderBy(['created_time' => SORT_DESC])->one();
    }
}
