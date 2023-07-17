<?php

namespace core\helpers;

use Yii;
use yii\db\BaseActiveRecord;

class HistoryEntityHelper
{
    public static function getActions()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => Yii::t('app', 'Event ' . BaseActiveRecord::EVENT_AFTER_INSERT),
            BaseActiveRecord::EVENT_AFTER_UPDATE => Yii::t('app', 'Event ' . BaseActiveRecord::EVENT_AFTER_UPDATE),
            BaseActiveRecord::EVENT_AFTER_DELETE => Yii::t('app', 'Event ' . BaseActiveRecord::EVENT_AFTER_DELETE),
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
