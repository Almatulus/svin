<?php

namespace core\models\warehouse;

use Yii;

/**
 * This is the model class for table "{{%warehouse_product_type}}".
 *
 * @property integer $id
 * @property string $name
 *
 */
class ProductType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_product_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    public static function map() {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}   
