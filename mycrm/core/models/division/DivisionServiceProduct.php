<?php

namespace core\models\division;

use core\models\warehouse\Product;
use Yii;

/**
 * This is the model class for table "{{%division_service_products}}".
 *
 * @property integer $id
 * @property integer $division_service_id
 * @property integer $product_id
 * @property double $quantity
 *
 * @property DivisionService $divisionService
 * @property Product $product
 */
class DivisionServiceProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%division_service_products}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'quantity'], 'required'],
            [['division_service_id', 'product_id'], 'integer'],
            [['quantity'], 'number', 'min' => 1],
            [['division_service_id'], 'exist', 'skipOnError' => true, 'targetClass' => DivisionService::className(), 'targetAttribute' => ['division_service_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'division_service_id' => Yii::t('app', 'Division Service ID'),
            'product_id' => Yii::t('app', 'Product'),
            'quantity' => Yii::t('app', 'Quantity'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionService()
    {
        return $this->hasOne(DivisionService::className(), ['id' => 'division_service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function extraFields()
    {
        return [
            'product',
            'divisionService'
        ];
    }
}
