<?php

namespace common\components;

use common\components\events\NoticeCounterEventHandler;
use common\components\events\PushNotificationEventHandler;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use Yii;

class PushNotification
{
    const CALLBACK_HISTORY = 'history';
    const CALLBACK_DIVISION_REVIEW = 'comment';

    /**
     * Sends popup message to user
     *
     * @param PushNotificationEventHandler $event
     */
    public static function sendNotification(PushNotificationEventHandler $event)
    {
        if ($event->customer->key_android) {
            $client = new Client();
            $client->setApiKey(Yii::$app->params['fcm_server_key']);
            $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

            $message = new Message();
            $message->addRecipient(new Device($event->customer->key_android));
            $message->setData([
                'title'       => $event->title,
                'message'     => $event->message,
                'callback'    => $event->callback,
                'division_id' => $event->division_id,
            ]);

            $sent = $client->send($message);

            if ($sent) {
                $notificationCounter = new NotificationCounter();
                $notificationCounter->trigger(NoticeCounterEventHandler::EVENT_NOTIFICATION_SENT,
                    new NoticeCounterEventHandler([
                        'type'        => NotificationCounter::NOTIFICATION_TYPE_PUSH,
                        'division_id' => $event->division_id
                    ])
                );
            }
        }

//        if ($event->customer->key_ios) {
//            $apnsGcm = Yii::$app->apnsGcm;
//            $apnsGcm->send(ApnsGcm::TYPE_APNS, $event->customer->key_ios, $event->message,
//                [
//                    'customerProperty' => 1
//                ],
//                [
//                    'sound' => 'default',
//                    'badge' => 1
//                ]
//            );
//        }
    }
}