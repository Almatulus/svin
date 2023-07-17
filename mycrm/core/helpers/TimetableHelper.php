<?php

namespace core\helpers;

class TimetableHelper
{
    const VIEW_DAY = "agendaDay";
    const VIEW_WEEK = "agendaWeek";
    const VIEW_MONTH = "month";

    public static function getViews()
    {
        return [
            self::VIEW_DAY,
            self::VIEW_WEEK,
            self::VIEW_MONTH,
        ];
    }

    public static function getIntervals()
    {
        return [
            '00:05:00' => "5 минут",
            '00:10:00' => "10 минут",
            '00:15:00' => "15 минут",
            '00:30:00' => "30 минут",
            '01:00:00' => "1 час",
        ];
    }
}