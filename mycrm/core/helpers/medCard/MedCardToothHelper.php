<?php

namespace core\helpers\medCard;

use core\models\medCard\MedCardTooth;

class MedCardToothHelper
{
    /**
     * @param $toothNumber
     *
     * @return int
     */
    public static function getType($toothNumber)
    {
        if (in_array($toothNumber, self::adultTeeth())) {
            return MedCardTooth::TYPE_ADULT;
        }

        return MedCardTooth::TYPE_CHILD;
    }

    /**
     * @return array
     */
    public static function allTeeth()
    {
        return array_merge(self::adultTeeth(), self::childTeeth());
    }

    /**
     * @return array
     */
    public static function adultTeeth()
    {
        $teeth = [];
        for ($i = 18; $i >= 11; $i--) {
            $teeth[] = $i;
        }
        for ($i = 21; $i <= 28; $i++) {
            $teeth[] = $i;
        }
        for ($i = 48; $i >= 41; $i--) {
            $teeth[] = $i;
        }
        for ($i = 31; $i <= 38; $i++) {
            $teeth[] = $i;
        }

        return $teeth;
    }

    /**
     * @return array
     */
    public static function childTeeth()
    {
        $teeth = [];
        for ($i = 55; $i >= 51; $i--) {
            $teeth[] = $i;
        }
        for ($i = 61; $i <= 65; $i++) {
            $teeth[] = $i;
        }
        for ($i = 85; $i >= 81; $i--) {
            $teeth[] = $i;
        }
        for ($i = 71; $i <= 75; $i++) {
            $teeth[] = $i;
        }

        return $teeth;
    }

    /**
     * @return array
     */
    public static function getDiagnoses()
    {
        return [
            1  => 'отсутствует',
            2  => 'корень',
            3  => 'кариес',
            4  => 'пульпит',
            5  => 'периодонтит',
            6  => 'пломбированный',
            7  => 'парадонтоз',
            8  => 'подвижность',
            9  => 'иск.коронка',
            10 => 'иск.зуб',
            11 => 'Флюороз',
            12 => 'Гипоплазия',
            13 => 'Зубные камни',
            14 => 'Имплант',
        ];
    }

    /**
     * @return array
     */
    public static function getDiagnosisAbbreviations()
    {
        return [
            2  => 'R',
            3  => 'C',
            4  => 'P',
            5  => 'Pt',
            6  => 'П',
            7  => 'А',
            9  => 'К',
            10 => 'И',
            11 => 'Ф',
            12 => 'ГП',
            13 => 'ЗК',
            14 => 'И',
        ];
    }

    /**
     * @return array
     */
    public static function getDiagnosisColors()
    {
        return [
            2  => '#F00',
            3  => '#F00',
            4  => '#F00',
            5  => '#F00',
            6  => '#FF0',
            9  => '#FF0',
            10 => '#FF0',
        ];
    }
}