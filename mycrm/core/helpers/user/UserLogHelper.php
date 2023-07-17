<?php

namespace core\helpers\user;

class UserLogHelper
{
    const ACTION_LOGGED_IN = 1;
    const ACTION_LOGGED_OUT = 2;

    /**
     * @return array
     */
    public static function all(): array
    {
        return [
            self::ACTION_LOGGED_IN  => \Yii::t('app', 'logged in'),
            self::ACTION_LOGGED_OUT => \Yii::t('app', 'logged out'),
        ];
    }

}