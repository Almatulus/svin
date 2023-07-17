<?php

namespace core\helpers;

use core\models\company\Company;
use Yii;

class CompanyHelper
{
    /**
     * @return array
     */
    public static function getWebCallStatus()
    {
        return [
            true => Yii::t('app', 'enabled'),
            false => Yii::t('app', 'disabled'),
        ];
    }

    /**
     * @return array
     */
    public static function getPublishStatuses()
    {
        return [
            Company::PUBLISH_TRUE => Yii::t('app', 'enabled'),
            Company::PUBLISH_FALSE => Yii::t('app', 'disabled'),
        ];
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            Company::STATUS_ENABLED => Yii::t('app', 'enabled'),
            Company::STATUS_DISABLED => Yii::t('app', 'disabled'),
        ];
    }

    /**
     * @param int $status
     *
     * @return array
     */
    public static function getStatusLabel(int $status)
    {
        $statuses = self::getStatuses();

        return isset($statuses[$status]) ? $statuses[$status] : null;
    }

    public static function getCalculationMethods()
    {
        return [
            Company::CALCULATE_STRAIGHT => Yii::t('app', 'Calculate straight'),
            Company::CALCULATE_REVERSE => Yii::t('app', 'Calculate reverse'),
        ];
    }

    /**
     * @param string $message
     * @return int
     */
    public static function estimateSmsPrice(string $message)
    {
        return self::estimateNumberOfSms($message) * Yii::$app->params['sms_cost'];
    }

    /**
     * @param string $message
     * @return int
     */
    public static function estimateNumberOfSms(string $message)
    {
        $hasNonLatinCharacters = preg_match("/[^a-zA-z0-9._?~!@#$%^&*()`\[\]{}\"'';:,.\/<>?| ]/", $message);
        if ($hasNonLatinCharacters) {
            $numberOfSms = intval(ceil(mb_strlen($message, 'UTF-8') / 70));
        } else {
            $numberOfSms = intval(ceil(mb_strlen($message, 'UTF-8') / 160));
        }
        return $numberOfSms;
    }

    /**
     * @return array
     */
    public static function getMessagingNames()
    {
        return [
            Company::MESSAGING_SMS => Yii::t('app', 'SMS'),
            Company::MESSAGING_WA => Yii::t('app', 'WhatsApp'),
        ];
    }

    public static function getMessagingName(int $type)
    {
        return isset(self::getMessagingNames()[$type]) ? self::getMessagingNames()[$type] : 'None';
    }
}
