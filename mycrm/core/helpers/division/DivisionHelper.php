<?php

namespace core\helpers\division;

use core\models\division\Division;
use core\models\ServiceCategory;
use Yii;

class DivisionHelper
{

    /**
     * @return array
     */
    public static function getDefaultCategories()
    {
        return [
            ServiceCategory::ROOT_STOMATOLOGY => [
                "Терапевтический прием",
                "Хирургический прием",
                "Ортодонтический прием",
                "Ортопедический прием",
                "Реставрация зубов",
                "Имплантация зубов",
                "Парадонтология",
                "Детская стоматология",
                "Рентген",
                "Без категории"
            ]
        ];
    }

    /**
     * Returns list of statuses
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            Division::STATUS_ENABLED  =>
                Yii::t('app', Division::STATUS_ENABLED_NAME),
            Division::STATUS_DISABLED =>
                Yii::t('app', Division::STATUS_DISABLED_NAME),
        ];
    }

    public static function getStatusLabel($status)
    {
        $statuses = self::getStatuses();
        if ( ! isset($statuses[$status])) {
            throw new \DomainException('Status not exists');
        }

        return $statuses[$status];
    }
}
