<?php

namespace core\models\division;

use Yii;

/**
 * This is the model class for table "{{%division_socials}}".
 *
 * @property integer $id
 * @property string $url
 * @property integer $type
 * @property integer $division_id
 *
 * @property Division $division
 */
class DivisionSocial extends \yii\db\ActiveRecord
{
    const TYPE_VKONTAKTE = 1;
    const TYPE_INSTAGRAM = 2;
    const TYPE_FACEBOOK  = 3;
    const TYPE_MAILRU    = 4;

    private static $types = [
        self::TYPE_VKONTAKTE => 'vkontakte',
        self::TYPE_INSTAGRAM => 'instagram',
        self::TYPE_FACEBOOK  => 'facebook',
        self::TYPE_MAILRU    => 'mailru',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%division_socials}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'type', 'division_id'], 'required'],
            [['type', 'division_id'], 'integer'],
            [['url'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url' => Yii::t('app', 'Url'),
            'type' => Yii::t('app', 'Type'),
            'division_id' => Yii::t('app', 'Division ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
    }

    /**
     * Save multiple models
     * @param Division $division
     * @param DivisionSocial[] $socials
     * @param array $delete_ids
     * @return bool weather saved successfully
     */
    public static function saveMultiple(Division $division, $socials, $delete_ids)
    {
        self::deleteAll(['id' => $delete_ids]);

        foreach($socials as $model) {
            $model->division_id = $division->id;
            if(!$model->save())
                return false;
        }
        return true;
    }
}
