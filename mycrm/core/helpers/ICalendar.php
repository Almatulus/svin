<?php

namespace core\helpers;

use core\helpers\order\OrderConstants;
use core\models\order\Order;

class ICalendar {

    /**
     * Generates calendar with enabled orders of specific user
     * in ICal format
     * @return string
     */
    public static function generate()
    {
        $dateFormat = "Ymd\THis";
        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Inlife Group//MyCRM//EN\r\n";

        $currentDate = new \DateTime();
        $events = Order::find()
            ->startFrom($currentDate)
            ->status(OrderConstants::STATUS_ENABLED)
            ->staff(\Yii::$app->user->identity->staff->id)
            ->orderBy('datetime DESC')
            ->all();

        foreach ($events as $key => $event) {
            /* @var $event Order */
            $startDate = date($dateFormat, strtotime($event->datetime));
            $endDate = new \DateTime($event->datetime);
            $endDate->modify("+" . $event->duration . " minutes");
            $endDate = date($dateFormat, strtotime($endDate->format("Y-m-d H:i:s")));

            $ical .= "BEGIN:VEVENT\r\n"
                . "SUMMARY:{$event->servicesTitle}-{$event->companyCustomer->customer->name}\r\n"
                . "DESCRIPTION:{$event->note}\r\n"
                . "UID:{$event->id}\r\n"
                . "DTSTART:{$startDate}\r\n"
                . "DTEND:{$endDate}\r\n"
                . "DTSTAMP:" . date($dateFormat, time()) . "\r\n"
                . "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR";

        return $ical;
    }
}