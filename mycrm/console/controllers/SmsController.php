<?php
namespace console\controllers;

use core\services\customer\CustomerRequestService;
use Yii;
use yii\console\Controller;

class SmsController extends Controller
{
    private $customerRequestService;

    public function __construct(
        $id,
        $module,
        CustomerRequestService $customerRequestService,
        $config = []
    ) {
        $this->customerRequestService = $customerRequestService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * This command echoes what you have entered as the message.
     * @param string $to receiver number
     * @param string $message text message
     */
    public function actionSend($to, $message)
    {
        Yii::$app->sms->send($to, $message);
    }

    public function actionSendRequest($request_id)
    {
        $this->customerRequestService->sendSms($request_id);
    }
}
