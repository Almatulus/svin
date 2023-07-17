<?php

namespace core\models\query;

use core\models\NewsLog;

/**
 * This is the ActiveQuery class for [[\core\models\NewsLog]].
 *
 * @see \core\models\NewsLog
 */
class NewsLogQuery extends \yii\db\ActiveQuery
{
    public function enabled()
    {
        return $this->andWhere(['{{%news_logs}}.status' => NewsLog::STATUS_ENABLED]);
    }

    /**
     * @inheritdoc
     * @return \core\models\NewsLog[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\NewsLog|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
