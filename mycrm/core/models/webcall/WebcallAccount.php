<?php

namespace core\models\webcall;

use core\models\division\Division;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%company_webcall_accounts}}".
 *
 * @property int $id
 * @property int $division_id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Division $division
 */
class WebcallAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_webcall_accounts}}';
    }

    /**
     * @param int $division_id
     * @param string $name
     * @param string $email
     * @return WebcallAccount
     */
    public static function add(int $division_id, string $name, string $email)
    {
        $model = new self();
        $model->name = $name;
        $model->email = $email;
        $model->division_id = $division_id;
        return $model;
    }

    /**
     * @inheritdoc
     * @return \core\models\webcall\query\WebcallAccountQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\webcall\query\WebcallAccountQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id', 'name', 'email'], 'required'],
            [['division_id'], 'default', 'value' => null],
            [['division_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'email'], 'string', 'max' => 255],
            [
                ['division_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Division::className(),
                'targetAttribute' => ['division_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'division_id' => Yii::t('app', 'Division ID'),
            'name'        => Yii::t('app', 'Name'),
            'email'       => Yii::t('app', 'Email'),
            'created_at'  => Yii::t('app', 'Created At'),
            'updated_at'  => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestampBehaviour' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression("NOW()")
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }
}
