<?php
namespace common\components\events;

use core\models\customer\Customer;
use yii\base\Event;

/**
 * @property Customer $customer
 */
class PushNotificationEventHandler extends Event
{
    public $title;
    public $message;
    public $customer;
    public $division_id;
    public $callback;

    const EVENT_ORDER_TIME_CHANGED = 'PUSH_NOTIFICATION_EVENT_ORDER_TIME_CHANGED';
    const EVENT_ORDER_STATUS_CONFIRMED = 'PUSH_NOTIFICATION_EVENT_ORDER_STATUS_CONFIRMED';
    const EVENT_ORDER_STATUS_FINISHED = 'PUSH_NOTIFICATION_EVENT_ORDER_STATUS_FINISHED';
}