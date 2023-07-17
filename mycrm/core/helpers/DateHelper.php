<?php

namespace core\helpers;

use Yii;

class DateHelper {

    const HOURS_FULL_PATTERN = '/^((0[0-9]|1[0-9]|2[0-3]):([0-5]\d))|(24:00)$/';

    /**
     * Creating date collection between two dates
     *
     * <code>
     * <?php
     * # Example 1
     * date_range("2014-01-01", "2014-01-20", "+1 day", "m/d/Y");
     *
     * # Example 2. you can use even time
     * date_range("01:00:00", "23:00:00", "+1 hour", "H:i:s");
     * </code>
     *
     * @param string $first any date, time or datetime format
     * @param string $last any date, time or datetime format
     * @param string $step
     * @param string $output_format
     * @return array
     */
    static function date_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {

        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while( $current <= $last ) {

            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }

    static function convertMinutesToHumanReadableFormat($initialMinutes)
    {
        $hours = floor($initialMinutes / 60);
        $minutes = ($initialMinutes % 60);

        $result = Yii::t('app',
            "{n, plural, one{# hour} few{# hours} many{# hours} other{# hours}}",
            ['n' => $hours]);

        $minutesText = Yii::t('app',
            "{n, plural, one{# minute} few{# minutes} many{# minutes} other{# minutes}}",
            ['n' => $minutes]);

        if($minutes > 0){
            $result = $hours > 0 ? $result . ", " . $minutesText : $minutesText;
        }

        if($initialMinutes === 0){
            $result = $minutesText;
        }

        return $result;
    }


    /**
     * @return array
     */
    public static function daysOfWeek(): array
    {
        return [
            1 => \Yii::t('app', 'Monday'),
            2 => \Yii::t('app', 'Tuesday'),
            3 => \Yii::t('app', 'Wednesday'),
            4 => \Yii::t('app', 'Thursday'),
            5 => \Yii::t('app', 'Friday'),
            6 => \Yii::t('app', 'Saturday'),
            7 => \Yii::t('app', 'Sunday'),
        ];
    }

    /**
     * @param int $day
     * @return mixed|null
     */
    public static function dayOfWeek(int $day)
    {
        return self::daysOfWeek()[$day] ?? null;
    }

    public static function dateOptions()
    {
        return [
            'displayFormat' => 'dd/MM/yyyy',
            'autoWidget'    => false,
            'widgetClass'   => 'yii\widgets\MaskedInput',
            'widgetOptions' => [
                'definitions' => [
                    'd' => [
                        'validator'    => '(0[1-9]|[12]\d|3[01])',
                        'cardinality'  => 2,
                        'prevalidator' => [
                            ['validator' => "[0-3]", 'cardinality' => 1],
                        ]
                    ],
                    'm' => [
                        'validator'    => '(0[1-9]|1[012])',
                        'cardinality'  => 2,
                        'prevalidator' => [
                            ['validator' => "[0-1]", 'cardinality' => 1],
                        ]
                    ],
                    'y' => [
                        'validator'    => '(19|20)\\d{2}',
                        'cardinality'  => 4,
                        'prevalidator' => [
                            ['validator' => "[12]", 'cardinality' => 1],
                            ['validator' => "(19|20)", 'cardinality' => 2],
                            ['validator' => "(19|20)\\d", 'cardinality' => 3]
                        ]
                    ],
                ],
                'mask'        => 'd/m/y',
                'options'     => ['placeholder' => "ДД/ММ/ГГГГ"],
            ]
        ];
    }

    /**
     * @param int $numberOfMonths
     * @return \DateTimeImmutable[]
     */
    public static function getPreviousMonths(int $numberOfMonths)
    {
        $date = new \DateTimeImmutable(date("Y-m-01"));
        $dates = [$date];
        for ($i = 1; $i < $numberOfMonths; $i++) {
            $dates[] = $date->modify("-{$i} months");
        }
        return $dates;
    }
}