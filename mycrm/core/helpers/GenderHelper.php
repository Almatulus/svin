<?php

namespace core\helpers;

use Yii;

class GenderHelper
{
    const GENDER_UNDEFINED = 1;
    const GENDER_MALE = 2;
    const GENDER_FEMALE = 3;

    /**
     * @return array
     */
    public static function getGenders()
    {
        return [
            GenderHelper::GENDER_UNDEFINED => Yii::t('app', 'Undefined'),
            GenderHelper::GENDER_MALE      => Yii::t('app', 'Male'),
            GenderHelper::GENDER_FEMALE    => Yii::t('app', 'Female'),
        ];
    }

    /**
     * @param integer $gender
     *
     * @return array
     */
    public static function getGenderLabel($gender)
    {
        $genders = self::getGenders();

        if ( ! isset($genders[$gender])) {
            throw new \DomainException('Gender not exists');
        }

        return $genders[$gender];
    }
}
