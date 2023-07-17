<?php

namespace common\components\events\order;

use core\models\order\Order;
use yii\base\Event;

class CheckoutOrderEvent extends Event
{
    const EVENT_NAME = "CHECKOUT_ORDER";

    public $name = self::EVENT_NAME;

    /** @var Order */
    public $order;
}