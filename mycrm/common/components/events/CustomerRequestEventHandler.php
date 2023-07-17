<?php
namespace common\components\events;

use yii\base\Event;

class CustomerRequestEventHandler extends Event
{
    public $order;
    public $receiver;
    public $template;

    const EVENT_ORDER_CREATED = 'CUSTOMER_REQUEST_EVENT_ORDER_CREATED';
    const EVENT_ORDER_UPDATED = 'CUSTOMER_REQUEST_EVENT_ORDER_UPDATED';
}