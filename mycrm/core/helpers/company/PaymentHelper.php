<?php

namespace core\helpers\company;

use core\models\CompanyPaymentLog;
use Yii;

class PaymentHelper
{
    /** ToDo Set type to cash and card payments */
    const CASH_ID = 1;
    const CARD_ID = 2;

    const CASH = 1;
    const CARD = 2;
    const CASHBACK = 3;
    const INSURANCE = 4;
    const DEPOSIT = 5;
    const CERTIFICATE = 6;
    const TRANSFER = 7;

    /**
     * @param int $key
     * @return string|null
     */
    public static function get(int $key)
    {
        return self::all()[$key] ?? null;
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        return [
            self::CASHBACK  => Yii::t('app', 'Cashback'),
            self::INSURANCE => Yii::t('app', 'insurance'),
            self::DEPOSIT   => Yii::t('app', 'Deposit'),
        ];
    }

    /**
     * Returns list of currencies
     */
    public static function getCurrencyList() {
        return [
            CompanyPaymentLog::CURRENCY_KZT => Yii::t('app', 'Currency tenge'),
        ];
    }

    /**
     * @return array
     */
    public static function notAccountable(): array
    {
        return [
            self::CASHBACK,
            self::INSURANCE,
            self::CERTIFICATE
        ];
    }
}
