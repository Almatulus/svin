<?php

namespace core\helpers\division;

class ServiceHelper
{
    const ONE_DAY = 1;
    const TWO_DAYS = 2;
    const THREE_DAYS = 3;
    const FOUR_DAYS = 4;
    const FIVE_DAYS = 5;
    const SIX_DAYS = 6;
    const SEVEN_DAYS = 7;
    const TWO_WEEKS = 8;
    const ONE_MONTH = 9;
    const THREE_MONTHS = 10;
    const SIX_MONTHS = 11;

    /**
     * @return array
     */
    public static function all(): array
    {
        return [
            self::ONE_DAY      => \Yii::t('app', '{n, plural, one{# day} few{# days} many{# days} other{# days}}',
                ['n' => 1]),
            self::TWO_DAYS     => \Yii::t('app', '{n, plural, one{# day} few{# days} many{# days} other{# days}}',
                ['n' => 2]),
            self::THREE_DAYS   => \Yii::t('app', '{n, plural, one{# day} few{# days} many{# days} other{# days}}',
                ['n' => 3]),
            self::FOUR_DAYS    => \Yii::t('app', '{n, plural, one{# day} few{# days} many{# days} other{# days}}',
                ['n' => 4]),
            self::FIVE_DAYS    => \Yii::t('app', '{n, plural, one{# day} few{# days} many{# days} other{# days}}',
                ['n' => 5]),
            self::SIX_DAYS     => \Yii::t('app', '{n, plural, one{# day} few{# days} many{# days} other{# days}}',
                ['n' => 6]),
            self::SEVEN_DAYS   => \Yii::t('app', '{n, plural, one{# day} few{# days} many{# days} other{# days}}',
                ['n' => 7]),
            self::TWO_WEEKS    => \Yii::t('app', '{n, plural, one{# week} few{# weeks} many{# weeks} other{# weeks}}',
                ['n' => 2]),
            self::ONE_MONTH    => \Yii::t('app',
                '{n, plural, one{# month} few{# months} many{# months} other{# months}}', ['n' => 1]),
            self::THREE_MONTHS => \Yii::t('app',
                '{n, plural, one{# month} few{# months} many{# months} other{# months}}', ['n' => 3]),
            self::SIX_MONTHS   => \Yii::t('app',
                '{n, plural, one{# month} few{# months} many{# months} other{# months}}', ['n' => 6]),
        ];
    }

    /**
     * @return array
     */
    public static function getIntervals(): array
    {
        return [
            self::ONE_DAY      => "1 day",
            self::TWO_DAYS     => "2 days",
            self::THREE_DAYS   => "3 days",
            self::FOUR_DAYS    => "4 days",
            self::FIVE_DAYS    => "5 days",
            self::SIX_DAYS     => "6 days",
            self::SEVEN_DAYS   => "7 days",
            self::TWO_WEEKS    => "2 weeks",
            self::ONE_MONTH    => "1 month",
            self::THREE_MONTHS => "3 months",
            self::SIX_MONTHS   => "6 months",
        ];
    }
}