<?php

namespace common\components;

use common\components\events\NoticeCounterEventHandler;
use core\models\company\Company;
use core\models\division\Division;
use yii\base\Component;

class NotificationCounter extends Component
{
    const NOTIFICATION_TYPE_EMAIL = 'email';
    const NOTIFICATION_TYPE_PUSH = 'push';
    const NOTIFICATION_TYPE_SMS = 'sms';

    public function init()
    {
        $this->on(NoticeCounterEventHandler::EVENT_NOTIFICATION_SENT, [$this, 'increment']);
    }


    /**
     * Count number of notifications
     *
     * @param NoticeCounterEventHandler $event
     */
    public function increment(NoticeCounterEventHandler $event)
    {
        $company = null;
        if ($event->company_id) {
            $company = Company::findOne($event->company_id);
        } else if ($event->division_id && ($division = Division::findOne($event->division_id)) !== null) {
            $company = $division->company;
        } else if ($event->type == self::NOTIFICATION_TYPE_EMAIL) {
            $company = \Yii::$app->user->identity->company;
        }

        if ($company !== null) {
            $noticesInfo = $company->noticesInfo;

            switch ($event->type) {
                case self::NOTIFICATION_TYPE_EMAIL:
                    $noticesInfo->updateCounters(['email_count' => 1]);
                    break;
                case self::NOTIFICATION_TYPE_PUSH:
                    $noticesInfo->updateCounters(['push_count' => 1]);
                    break;
                case self::NOTIFICATION_TYPE_SMS:
                    $noticesInfo->updateCounters(['sms_count' => 1]);
                    break;
            }
        }
    }
}