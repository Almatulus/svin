<?php

namespace core\helpers;

use core\models\order\Order;
use Yii;

class OrderHelper
{
    /**
     * Returns list of notification timeout time
     *
     * @return array
     */
    public static function getNotificationTimeList()
    {
        return [
            0   => Yii::t('app', 'Do not notify'),
            1   => "1 час",
            2   => "2 часа",
            3   => "3 часа",
            4   => "4 часа",
            5   => "5 часов",
            6   => "6 часов",
            9   => "9 часов",
            12  => "12 часов",
            15  => "15 часов",
            18  => "18 часов",
            21  => "21 час",
            24  => "24 часа",
            48  => "2 дня",
            72  => "3 дня",
            120 => "5 дней",
            168 => "7 дней",
        ];
    }

    /**
     * @return array
     */
    public static function getCssClasses()
    {
        return [
            "color1"  => "blue",
            "color5"  => "coral",
            "color3"  => "yellow",
            "color4"  => "olive",
            "color16" => "celeste",
            "color12" => "turquoise",
            "color10" => "pink",
            "color15" => "beige",
            "color11" => "ochre",
            "color2"  => "cornflower",
            "color6"  => "plum",
            "color13" => "light blue",
            "color9"  => "pistachio",
            "color7"  => "lavender",
            "color14" => "cream",
            "color8"  => "emerald",
        ];
    }

    /**
     * @param Order[] $models
     *
     * @return integer
     */
    public static function getTotalPaidSum($models)
    {
        return array_reduce($models, function ($result, Order $model) {
            return $result + $model->getIncomeCash();
        }, 0);
    }
}
