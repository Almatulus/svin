<?php
namespace common\components\events;

use yii\base\Event;

class NotificationEventHandler extends Event
{
    public $message;
    public $type;
    public $users = [];

    const EVENT_ORDER_CREATED = 'NOTIFICATION_EVENT_ORDER_CREATED';
}