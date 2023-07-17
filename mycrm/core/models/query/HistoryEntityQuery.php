<?php

namespace core\models\query;

/**
 * This is the ActiveQuery class for [[\core\models\HistoryEntity]].
 *
 * @see \core\models\HistoryEntity
 */
class HistoryEntityQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \core\models\HistoryEntity[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\HistoryEntity|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $table_name
     * @return $this
     */
    public function table($table_name)
    {
        return $this->andWhere(['table_name' => $table_name]);
    }
}
