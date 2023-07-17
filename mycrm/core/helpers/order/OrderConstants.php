<?php

namespace core\helpers\order;

class OrderConstants
{
    /**
     * Types
     */
    const TYPE_APPLICATION = 0;
    const TYPE_MANUAL = 1;
    const TYPE_SITE = 2;

    /**
     * Statuses
     */
    const STATUS_ENABLED = 0;
    const STATUS_DISABLED = 1;
    const STATUS_FINISHED = 3;
    const STATUS_CANCELED = 4;
    const STATUS_WAITING = 5;

    /**
     * Notification actions
     */
    const NOTIFY_FALSE = 0;
    const NOTIFY_TRUE = 1;
    const NOTIFY_DONE = 2;

    const STATISTICS_EXCLUDED_COMPANIES = [181, 56, 30];

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_APPLICATION => \Yii::t('app', 'application'),
            self::TYPE_MANUAL => \Yii::t('app', 'manual'),
            self::TYPE_SITE => \Yii::t('app', 'site'),
        ];
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ENABLED => \Yii::t('app', 'order status created'),
            self::STATUS_DISABLED => \Yii::t('app', 'order status disabled'),
            self::STATUS_FINISHED => \Yii::t('app', 'order status finished'),
            self::STATUS_CANCELED => \Yii::t('app', 'order status disabled'),
            self::STATUS_WAITING => \Yii::t('app', 'order status waiting'),
        ];
    }

    /**
     * @param $status
     *
     * @return string|null
     */
    public static function getStatusLabel($status)
    {
        $statuses = self::getStatuses();
        return $statuses[$status] ?: null;
    }

    /**
     * @return array
     */
    public static function getUniqueStatuses()
    {
        return [
            self::STATUS_ENABLED  => \Yii::t('app', 'order status created'),
            self::STATUS_DISABLED => \Yii::t('app', 'order status disabled'),
            self::STATUS_FINISHED => \Yii::t('app', 'order status finished'),
            self::STATUS_WAITING  => \Yii::t('app', 'order status waiting'),
        ];
    }
}