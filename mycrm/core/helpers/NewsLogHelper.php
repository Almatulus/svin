<?php

namespace core\helpers;

use core\models\NewsLog;
use Yii;

class NewsLogHelper
{
    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            NewsLog::STATUS_DISABLED => Yii::t('app', 'disabled'),
            NewsLog::STATUS_ENABLED => Yii::t('app', 'enabled'),
        ];
    }
}
