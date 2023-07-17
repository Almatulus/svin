<?php

namespace core\helpers;

use Yii;

class ScheduleTemplateHelper
{
    const TYPE_DAYS_OF_WEEK = 1;
    const TYPE_THREE_TO_TWO = 2;
    const TYPE_ODD_EVEN = 3;

    const PERIOD_ONE_WEEK = 1;
    const PERIOD_TWO_WEEKS = 2;
    const PERIOD_ONE_MONTH = 3;
    const PERIOD_TWO_MONTHS = 4;

    /**
     * @return array
     */
    public static function types(): array
    {
        return [
            self::TYPE_DAYS_OF_WEEK => Yii::t('app', 'By days of week'),
            self::TYPE_THREE_TO_TWO => Yii::t('app', '3 workdays/2 holidays'),
            self::TYPE_ODD_EVEN     => Yii::t('app', 'By odd/even days')
        ];
    }

    /**
     * @return array
     */
    public static function periods(): array
    {
        return [
            self::PERIOD_ONE_WEEK   => Yii::t('app',
                '{n, plural, one{# week} few{# weeks} many{# weeks} other{# weeks}}', ['n' => 1]),
            self::PERIOD_TWO_WEEKS  => Yii::t('app',
                '{n, plural, one{# week} few{# weeks} many{# weeks} other{# weeks}}', ['n' => 2]),
            self::PERIOD_ONE_MONTH  => Yii::t('app',
                '{n, plural, one{# month} few{# months} many{# months} other{# months}}', ['n' => 1]),
            self::PERIOD_TWO_MONTHS => Yii::t('app',
                '{n, plural, one{# month} few{# months} many{# months} other{# months}}', ['n' => 2]),
        ];
    }

    /**
     * @param int $index
     * @return string
     */
    public static function periodValue(int $index): string
    {
        return self::periodValues()[$index] ?? null;
    }

    /**
     * @return array
     */
    public static function periodValues(): array
    {
        return [
            self::PERIOD_ONE_WEEK   => '1 week',
            self::PERIOD_TWO_WEEKS  => '2 weeks',
            self::PERIOD_ONE_MONTH  => '1 month',
            self::PERIOD_TWO_MONTHS => '2 months',
        ];
    }

    /**
     * @param int|null $type
     * @return mixed
     */
    public static function intervals(int $type = null)
    {
        return self::allIntervals()[$type] ?? self::allIntervals()[self::TYPE_DAYS_OF_WEEK];
    }

    /**
     * @return array
     */
    public static function allIntervals(): array
    {
        return [
            self::TYPE_DAYS_OF_WEEK => DateHelper::daysOfWeek(),
            self::TYPE_THREE_TO_TWO => [
                1 => Yii::t('app', 'First'),
                2 => Yii::t('app', 'Second'),
                3 => Yii::t('app', 'Third'),
            ],
            self::TYPE_ODD_EVEN     => [
                1 => Yii::t('app', 'Odd'),
                2 => Yii::t('app', 'Even'),
            ]
        ];
    }
}