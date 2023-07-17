<?php

namespace core\models;

use core\models\user\User;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\Request;
use yii\web\Response;

/**
 * This is the model class for table "{{%api_history}}".
 *
 * @property integer $id
 * @property string $ip
 * @property string $created_time
 * @property string $url
 * @property integer $user_id
 * @property float $running_time
 * @property string $request_header
 * @property string $request_query
 * @property string $request_body
 * @property string $response_header
 * @property string $response_body
 * @property integer $response_status_code
 * @property string $request_method
 */
class ApiHistory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%api_history}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ip' => Yii::t('app', 'Ip'),
            'created_time' => Yii::t('app', 'Created Time'),
            'url' => Yii::t('app', 'Url'),
            'user_id' => Yii::t('app', 'User ID'),
            'running_time' => Yii::t('app', 'Running time'),
            'request_header' => Yii::t('app', 'Request Header'),
            'request_body' => Yii::t('app', 'Request Body'),
            'request_query' => Yii::t('app', 'Request Query'),
            'response_header' => Yii::t('app', 'Response Header'),
            'response_body' => Yii::t('app', 'Response Body'),
            'response_status_code' => Yii::t('app', 'Response Status Code'),
            'request_method' => Yii::t('app', 'Request Method'),
        ];
    }

    /**
     * Create Api History model
     *
     * @param Request  $request
     * @param Response $response
     * @param float    $running_time
     * @param User     $user
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public static function log(
        Request $request,
        Response $response,
        float $running_time,
        User $user = null
    ) {
        $model               = new ApiHistory();
        $model->created_time = date("Y-m-d H:i:s");
        $model->ip           = $request->getUserIP();
        $model->url          = $request->getAbsoluteUrl();
        $model->running_time = $running_time;

        $model->request_header  = Json::encode($request->getHeaders()->toArray());
        $model->request_query   = Json::encode($request->queryParams);
        $model->request_body    = Json::encode($request->bodyParams);
        $model->request_method  = $request->method;

        $model->response_header = Json::encode($response->getHeaders()->toArray());
        $model->response_status_code = $response->getStatusCode();

        if ($user !== null) {
            $model->user_id = $user->id;
        }

        if ( ! $model->insert(false)) {
            throw new \DomainException('Api History not saved');
        }
    }
}
