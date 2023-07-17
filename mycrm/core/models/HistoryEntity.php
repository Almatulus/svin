<?php

namespace core\models;

use core\models\user\User;
use yii\db\BaseActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "history".
 *
 * @property integer $id
 * @property string $initiator
 * @property string $ip
 * @property string $event
 * @property string $class
 * @property string $table_name
 * @property string $row_id
 * @property array $log
 * @property string $created_time
 * @property integer $updated_at
 */
class HistoryEntity extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%history}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\query\HistoryEntityQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\query\HistoryEntityQuery(get_called_class());
    }

    public static function getEventsList()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => BaseActiveRecord::EVENT_AFTER_INSERT,
            BaseActiveRecord::EVENT_AFTER_UPDATE => BaseActiveRecord::EVENT_AFTER_UPDATE,
            BaseActiveRecord::EVENT_AFTER_DELETE => BaseActiveRecord::EVENT_AFTER_DELETE,
        ];
    }

    public function afterFind()
    {
        $this->log = array_filter(Json::decode($this->getAttribute('log')));
        parent::afterFind();
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        if (empty($this->initiator)) {
            return null;
        }

        return User::findOne(['id' => $this->initiator]);
    }
}
