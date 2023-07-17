<?php

namespace console\controllers;

use core\models\order\Order;
use yii\console\Controller;

class PushController extends Controller
{
    /**
     * @param int $shift - hours
     * @param int $interval - minutes
     */
    // 0 * * * * php /home/project/crm/yii push/notify-staff-about-visit > /dev/null
    public function actionNotifyStaffAboutVisit(int $shift = 1, int $interval = 15)
    {
        $minDatetime = (new \DateTimeImmutable())->modify("+{$shift} hours");
        $maxDatetime = $minDatetime->modify("+{$interval} minutes");

        $orders = Order::find()->enabled()
            ->with(['staff.user'])
            ->andWhere(['>=', 'datetime', $minDatetime->format("Y-m-d H:i")])
            ->andWhere(['<', 'datetime', $maxDatetime->format("Y-m-d H:i")]);

        foreach ($orders->each(100) as $order) {
            /** @var Order $order */
            $device_key = $order->staff->user->device_key ?? null;
            if ($device_key) {
                \Yii::$app->pushService->sendTemplate(
                    $order->staff->user->device_key,
                    "Напоминание о записи",
                    $order->getTextInfo(),
                    [
                        "title"       => "Напоминание о записи",
                        "body"        => $order->datetime . "\n" . $order->getTextInfo(),
                        "order_id"    => $order->id,
                        "datetime"    => $order->datetime,
                        "division_id" => $order->division_id,
                        "staff_id"    => $order->staff_id,
                    ]
                );
            }
        }
    }
}