<?php

namespace core\models\company;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%company_tariffs}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $staff_qty
 * @property integer $price
 * @property string $created_at
 * @property string $updated_at
 * @property boolean $is_deleted
 *
 * @property Company[] $companies
 */
class Tariff extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_tariffs}}';
    }

    /**
     * @return array
     */
    public static function map()
    {
        return ArrayHelper::map(self::find()->enabled()->all(), 'id', 'name');
    }

    /**
     * @inheritdoc
     * @return \core\models\company\query\TariffQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\company\query\TariffQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'staff_qty', 'price'], 'required'],
            [['staff_qty', 'price'], 'integer'],
            ['name', 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe'],
            [['is_deleted'], 'boolean'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'value' => function () {
                    return date('Y-m-d H:i:s');
                }
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
            'name'       => Yii::t('app', 'Name'),
            'staff_qty'  => Yii::t('app', 'Staff Quantity'),
            'price'      => Yii::t('app', 'Price'),
            'created_at' => Yii::t('app', 'Created at'),
            'updated_at' => Yii::t('app', 'Updated at'),
            'is_deleted' => Yii::t('app', 'Is Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::className(), ['tariff_id' => 'id']);
    }
}
