<?php

namespace core\models\company;

use Yii;

/**
 * This is the model class for table "{{%company_notifications}}".
 *
 * @property integer $company_id
 * @property integer $type
 */
class Notification extends \yii\db\ActiveRecord
{
    const MIN_BALANCE = 100;

    const TYPE_MIN_BALANCE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_notifications}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'type'], 'required'],
            [['company_id', 'type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => Yii::t('app', 'Company ID'),
            'type'       => Yii::t('app', 'Type'),
        ];
    }
}
