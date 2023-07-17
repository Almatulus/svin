<?php

namespace api\modules\v2\controllers\user;

use api\modules\v2\controllers\BaseController;
use core\models\order\Order;
use core\models\Staff;
use core\services\user\UserService;
use yii\base\DynamicModel;
use yii\base\Module;

class PushController extends BaseController
{
    public $modelClass = false;

    /**
     * @var UserService
     */
    private $service;

    /**
     * PushController constructor.
     * @param string $id
     * @param Module $module
     * @param UserService $userService
     * @param array $config
     */
    public function __construct($id, Module $module, UserService $userService, array $config = [])
    {
        $this->service = $userService;

        parent::__construct($id, $module, $config);
    }

    /**
     * @return DynamicModel
     */
    public function actionKey()
    {
        $model = new DynamicModel(['key']);
        $model->addRule('key', 'required');
        $model->addRule('key', 'string', ['max' => '255']);
        $model->attributes = \Yii::$app->request->bodyParams;

        if ($model->validate()) {
            $this->service->addPushKey(\Yii::$app->user->id, $model->key);
            return $model->key;
        }

        return $model;
    }

    /**
     * Test push notifications
     */
    public function actionTest()
    {
        if (\Yii::$app->user->identity->device_key) {

            $staff = Staff::findOne(['user_id' => \Yii::$app->user->id]);

            if (!$staff) {
                throw new \DomainException("There isn't staff for this account. Create one.");
            }

            /** @var Order $lastOrder */
            $lastOrder = Order::find()->staff($staff->id)->enabled()->orderBy('datetime DESC')->one();

            if (!$lastOrder) {
                throw new \DomainException("There isn't enabled order. Create one.");
            }

            \Yii::$app->pushService->sendTemplate(
                \Yii::$app->user->identity->device_key,
                "Напоминание о записи",
                $lastOrder->getTextInfo(),
                [
                    "title"       => "Напоминание о записи",
                    "body"        => $lastOrder->getTextInfo(),
                    "order_id"    => $lastOrder->id,
                    "datetime"    => $lastOrder->datetime,
                    "division_id" => $lastOrder->division_id,
                    "staff_id"    => $lastOrder->staff_id,
                ]
            );
        } else {
            throw new \DomainException("Please specify device key.");
        }
    }
}