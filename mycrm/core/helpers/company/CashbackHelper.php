<?php

namespace core\helpers\company;

class CashbackHelper
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    const TYPE_IN = 1;
    const TYPE_OUT = 2;

    /**
     * @param int $type
     * @return mixed|null
     */
    public static function getTypeLabel(int $type)
    {
        return self::getTypes()[$type] ?? null;
    }

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_IN  => \Yii::t('app', 'Поступление'),
            self::TYPE_OUT => \Yii::t('app', 'Отчисление'),
        ];
    }
}