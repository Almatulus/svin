<?php
namespace console\controllers;

use core\services\notification\ClientSMSNotificationService;
use yii\console\Controller;

class ClientVisitNotificationController extends Controller
{
    private $service;

    public function __construct($id, $module, ClientSMSNotificationService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * This command notifies customers before visit. This command should be run by 20 minutes interval
     *
     * @throws \Exception
     */
    // */20 * * * * php /home/project/crm/yii client-visit-notification/check-all > /dev/null
    public function actionCheckAll()
    {
        $this->service->sendFutureVisitNotifications();
    }
}
