<?php

namespace common\components;

use common\components\events\NotificationEventHandler;
use core\models\user\User;

class Notification
{

    const TYPE_MESSAGE = 1;
    const TYPE_NOTIFICATION = 2;

    /**
     * Sends popup message to user
     * @param NotificationEventHandler $event
     */
    public static function sendNotification(NotificationEventHandler $event)
    {
        if (!empty($event->users)) {
            $users = User::find()->filterWhere(['id' => $event->users])->all();
            static::show($users, $event->message);
        }
    }

    /**
     * Send message
     * @param User[] $users
     * @param string $massage
     */
    public static function show($users, $massage)
    {
        foreach ($users as $user) {
            if ($user instanceof User) {
                shell_exec("(php " . \Yii::$app->basePath . "/../yii notification/send {$user->id} '{$massage}') &");
            }
        }
    }

}