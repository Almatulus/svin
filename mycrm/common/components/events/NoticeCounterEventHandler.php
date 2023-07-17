<?php

namespace common\components\events;

use yii\base\Event;

class NoticeCounterEventHandler extends Event
{
    public $type;
    public $company_id;
    public $division_id;

    const EVENT_NOTIFICATION_SENT = 'NOTIFICATION_SENT';
}