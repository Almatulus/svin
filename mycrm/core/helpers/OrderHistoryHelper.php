<?php

namespace core\helpers;

use core\models\order\OrderHistory;
use Yii;

class OrderHistoryHelper
{
    public static function getActions()
    {
        return [
            OrderHistory::ACTION_CREATE   =>
                Yii::t('app',
                    'OrderHistory action '.OrderHistory::ACTION_CREATE),
            OrderHistory::ACTION_UPDATE   => Yii::t('app',
                'OrderHistory action '.OrderHistory::ACTION_UPDATE),
            OrderHistory::ACTION_CHECKOUT => Yii::t('app',
                'OrderHistory action '.OrderHistory::ACTION_CHECKOUT),
            OrderHistory::ACTION_DISABLE  => Yii::t('app',
                'OrderHistory action '.OrderHistory::ACTION_DISABLE),
            OrderHistory::ACTION_RESET    => Yii::t('app',
                'OrderHistory action '.OrderHistory::ACTION_RESET),
            OrderHistory::ACTION_CANCEL   => Yii::t('app',
                'OrderHistory action '.OrderHistory::ACTION_CANCEL),
        ];
    }

    /**
     * @param integer
     *
     * @return string
     */
    public static function getActionLabel($action)
    {
        $actions = self::getActions();
        if ( ! isset($actions[$action])) {
            throw new \DomainException('Action not found');
        }

        return $actions[$action];
    }
}
