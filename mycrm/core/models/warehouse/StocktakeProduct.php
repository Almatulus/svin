<?php

namespace core\models\warehouse;

use Yii;

/**
 * This is the model class for table "{{%warehouse_stocktake_product}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $stocktake_id
 * @property double $purchase_price
 * @property double $recorded_stock_level
 * @property double $actual_stock_level
 * @property boolean $apply_changes
 *
 * @property Product $product
 */
class StocktakeProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_stocktake_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'purchase_price', 'recorded_stock_level', 'actual_stock_level', 'stocktake_id'], 'required'],
            [['product_id'], 'integer'],
            [['purchase_price', 'recorded_stock_level', 'actual_stock_level'], 'number', 'min' => 0],
            
            ['apply_changes', 'boolean'],
            ['apply_changes', 'default', 'value' => false],
            
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['stocktake_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stocktake::className(), 'targetAttribute' => ['stocktake_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'purchase_price' => Yii::t('app', 'Purchase price'),
            'recorded_stock_level' => Yii::t('app', 'Recorded stock level'),
            'actual_stock_level' => Yii::t('app', 'Actual stock level'),
            'balanceText' => Yii::t('app', 'Products difference'),
            'estimatedVarianceText' => Yii::t('app', 'Estimated variance value')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStocktake()
    {
        return $this->hasOne(Stocktake::className(), ['id' => 'product_id']);
    }

    /**
     * @return float|null
     */
    public function getBalance()
    {
        return $this->actual_stock_level 
        ? $this->actual_stock_level - $this->recorded_stock_level
        : null;
    }

    /**
     * @return mixed|null
     */
    public function getEstimatedVarianceValue()
    {
        return $this->actual_stock_level
        ? $this->balance * $this->purchase_price
        : null;
    }

    /**
     * @return mixed|string
     */
    public function getBalanceText()
    {
        if ($this->balance > 0) {
            return "+" . $this->balance;
        } 
        return $this->balance;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getEstimatedVarianceText()
    {
        if ($this->estimatedVarianceValue > 0) {
            return "+" . Yii::$app->formatter->asDecimal($this->estimatedVarianceValue);
        }
        return Yii::$app->formatter->asDecimal($this->estimatedVarianceValue);
    }
}
