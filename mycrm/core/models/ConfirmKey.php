<?php

namespace core\models;

use core\helpers\Security;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%confirm_keys}}".
 *
 * @property integer $id
 * @property integer $status
 * @property string  $code
 * @property string  $username
 * @property string  $expired_at
 */
class ConfirmKey extends ActiveRecord
{
    const EXPIRE_TIME = 7 * 24 * 3600; // 1 week

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%confirm_keys}}';
    }

    public static function add($username)
    {
        $model             = new ConfirmKey();
        $model->status     = self::STATUS_ENABLED;
        $model->code       = Security::random_str(6, "0123456789");
        $model->username   = $username;
        $model->expired_at = date('Y-m-d H:i:s', self::EXPIRE_TIME + time());

        return $model;
    }

    public function disable()
    {
        $this->status = self::STATUS_DISABLED;
    }
}
