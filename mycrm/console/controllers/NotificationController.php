<?php
namespace console\controllers;

use core\services\commands\NotificationService;
use DateTime;
use yii\console\Controller;

class NotificationController extends Controller
{
    private $notificationService;

    public function __construct($id, $module, NotificationService $notificationService, $config = [])
    {
        $this->notificationService = $notificationService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * This command congratulates customers birthdays. This command should be run at 11am each day
     */
    // 0 11 * * * php /home/project/crm/yii notification/birthday > /dev/null
    public function actionBirthday()
    {
        $current_data = new DateTime();
        echo "------- notification sending stated '{$current_data->format("Y-m-d")}' -------\n\n";

        $messagesSentCount = $this->notificationService->notifyBirthDay();

        echo "!!! {$messagesSentCount} customers were notified !!! \n\n";
        echo "------- notification sending finished -------\n";
    }

    /**
     * This command sends delayed notifications about health examination. This command should be run each day
     */
    // 0 0 * * * php /home/project/crm/yii notification/delayed > /dev/null
    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionDelayed()
    {
        $current_data = new DateTime();
        echo "------- notification sending stated '{$current_data->format("Y-m-d")}' -------\n\n";

        $messagesSentCount = $this->notificationService->sendDelayedNotifications();

        echo "!!! {$messagesSentCount} customers were notified !!! \n\n";
        echo "------- notification sending finished -------\n";

    }

    /**
     * This command sends notifications to customer whose last visit was N days/months ago.
     */
    // 0 1 * * * php /home/project/crm/yii notification/last-visit > /dev/null
    public function actionLastVisit()
    {
        $current_data = new DateTime();
        echo "------- notification sending stated '{$current_data->format("Y-m-d")}' -------\n\n";

        $messagesSentCount = $this->notificationService->notifySinceLastVisit();

        echo "!!! {$messagesSentCount} customers were notified !!! \n\n";
        echo "------- notification sending finished -------\n";
    }

    /**
     * Sends notification
     * @param integer $user
     * @param string $message
     */
    public function actionSend($user, $message)
    {
        $data = [
            'id' => $user,
            'msg' => $message,
        ];
        $this->send($data);
    }

    /**
     * Send jgrowl notification
     * @param $user
     * @param $message
     * @param $group
     */
    public function actionSendJgrowl($user, $message, $group)
    {
        $data = [
            'id' => $user,
            'msg' => $message,
            'type' => 'jGrowl',
            'group' => $group
        ];
        $this->send($data);
    }

    /**
     * Send request via curl
     * @param array $data
     */
    private function send(Array $data)
    {
        $data = http_build_query($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost:8001/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);
        curl_close($ch);
    }
}