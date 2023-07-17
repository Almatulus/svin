<?php

namespace core\models\user;

use core\helpers\user\UserLogHelper;
use Yii;

/**
 * This is the model class for table "{{%user_logs}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $datetime
 * @property integer $action
 *
 * @property User $user
 */
class UserLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_logs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'datetime', 'action', 'ip_address', 'user_agent'], 'required'],
            [['user_id', 'action'], 'integer'],
            ['action', 'in', 'range' => array_keys(UserLogHelper::all())],
            [['datetime'], 'safe'],
            [['ip_address', 'user_agent'], 'string', 'max' => 255],
            [
                ['user_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => User::className(),
                'targetAttribute' => ['user_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'user_id'    => Yii::t('app', 'User'),
            'ip_address' => Yii::t('app', 'Ip Address'),
            'user_agent' => Yii::t('app', 'User Agent'),
            'datetime'   => Yii::t('app', 'Datetime'),
            'action'     => Yii::t('app', 'Action'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return null
     */
    public function getActionLabel()
    {
        return UserLogHelper::all()[$this->action] ?? null;
    }
}
