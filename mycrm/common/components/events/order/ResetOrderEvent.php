<?php

namespace common\components\events\order;

use core\models\order\Order;
use yii\base\Event;

class ResetOrderEvent extends Event
{
    const EVENT_NAME = "RESET_ORDER";

    public $name = self::EVENT_NAME;

    /** @var Order */
    public $order;
}