<?php

namespace core\helpers\customer;

use Yii;

class RequestTemplateHelper
{
    const HOURMINUTES = "HOURMINUTES";
    const DATE = "DATE";
    const LINK = "LINK";
    const CLIENT_NAME = "CLIENT_NAME";
    const CLIENT_PHONE = "CLIENT_PHONE";
    const DISCOUNT = "DISCOUNT";
    const COMPANY_NAME = "COMPANY_NAME";
    const SERVICE_TITLE = "SERVICE_TITLE";
    const MASTER_NAME = "MASTER_NAME";
    const DATETIME = "DATETIME";
    const COMPANY_ADDRESS = "COMPANY_ADDRESS";
    const CONTACT_PHONE = "CONTACT_PHONE";
    const ORDER_KEY = "ORDER_KEY";
    const DIVISION_NAME = "DIVISION_NAME";
    const DIVISION_ADDRESS = "DIVISION_ADDRESS";

    const TYPE_DEFAULT = 1;
    const TYPE_DELAYED = 2;

    const QUANTITY_TYPE_DAYS = 1;
    const QUANTITY_TYPE_MONTHS = 2;

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            self::CLIENT_NAME => Yii::t('app', 'Client Name'),
            self::CLIENT_PHONE => Yii::t('app', 'Customer Phone'),
            self::COMPANY_ADDRESS => Yii::t('app', 'Division Address'),
            self::COMPANY_NAME => Yii::t('app', 'Company'),
            self::CONTACT_PHONE => Yii::t('app', 'Contact Phone'),
            self::DATE => Yii::t('app', 'Date'),
            self::DATETIME => Yii::t('app', 'Datetime'),
            self::DIVISION_NAME => Yii::t('app', 'Division ID'),
            self::DIVISION_ADDRESS => Yii::t('app', 'Division Address'),
            self::DISCOUNT => Yii::t('app', 'Discount'),
            self::HOURMINUTES => Yii::t('app', 'Hourminutes'),
            self::LINK => Yii::t('app', 'Link'),
            self::MASTER_NAME => Yii::t('app', 'Staff ID'),
            self::ORDER_KEY => Yii::t('app', 'Order Key'),
            self::SERVICE_TITLE => Yii::t('app', 'Service Title'),
        ];
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_DEFAULT => Yii::t('app', 'Default'),
            self::TYPE_DELAYED => Yii::t('app', 'Delayed'),
        ];
    }

    /**
     * @return array
     */
    public static function getQuantityTypesLabels(): array
    {
        return [
            self::QUANTITY_TYPE_DAYS   => Yii::t('app', 'Days'),
            self::QUANTITY_TYPE_MONTHS => Yii::t('app', 'Months'),
        ];
    }

    /**
     * @return array
     */
    public static function getQuantityTypes()
    {
        return [
            self::QUANTITY_TYPE_DAYS   => 'days',
            self::QUANTITY_TYPE_MONTHS => 'months',
        ];
    }

    /**
     * @param int $type
     * @return mixed|null
     */
    public static function getQuantityType(int $type)
    {
        return self::getQuantityTypes()[$type] ?? null;
    }
}