<?php

namespace core\models\warehouse;

use core\calculators\IProduct;
use core\models\company\Company;
use core\models\division\Division;
use core\models\warehouse\query\ProductQuery;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * ToDo consider deleting package_size and stock_unit_id
 * This is the model class for table "{{%warehouse_product}}".
 *
 * @property integer $id
 * @property string $barcode
 * @property string $description
 * @property double $quantity
 * @property integer $division_id
 * @property double $min_quantity
 * @property string $name
 * @property double $package_size
 * @property double $purchase_price
 * @property double $price
 * @property string $sku
 * @property integer $stock_unit_id
 * @property double $vat
 * @property integer $company_id
 * @property integer $category_id
 * @property integer $unit_id
 * @property integer $manufacturer_id
 * @property integer $status
 *
 * @property Category $category
 * @property Company $company
 * @property Manufacturer $manufacturer
 * @property ProductType[] $productTypes
 * @property ProductUnit $unit
 */
class Product extends \yii\db\ActiveRecord implements IProduct
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    const TYPE_FOR_SALE = 1;
    const TYPE_FOR_USE = 2;

    /**
     * Add products amount
     * @param integer $amount
     */
    public function addQuantity($amount)
    {
        $this->quantity += $amount;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['quantity', 'min_quantity', 'price', 'purchase_price', 'vat'], 'number'],
            [['division_id', 'name', 'quantity', 'unit_id'], 'required'],
            [['category_id', 'division_id', 'manufacturer_id', 'status', 'unit_id'], 'integer'],
            [['barcode', 'name', 'sku'], 'string', 'max' => 255],

//            ['name', 'unique', 'targetAttribute' => ['name', 'division_id'], 'message' => 'Товар с таким наименованием для данного заведения уже существует'],

            [['vat', 'quantity', 'price', 'purchase_price'], 'default', 'value' => 0],

            ['status', 'default', 'value' => self::STATUS_ENABLED],

            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['manufacturer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Manufacturer::className(), 'targetAttribute' => ['manufacturer_id' => 'id']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductUnit::className(), 'targetAttribute' => ['unit_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'barcode' => Yii::t('app', 'Barcode'),
            'description' => Yii::t('app', 'Description'),
            'division_id' => Yii::t('app', 'Division ID'),
            'quantity' => Yii::t('app', 'Stock level'),
            'min_quantity' => Yii::t('app', 'Minimum stock level'),
            'name' => Yii::t('app', 'Name'),
            'package_size' => Yii::t('app', 'Package size'),
            'price' => Yii::t('app', 'Selling price'),
            'purchase_price' => Yii::t('app', 'Purchase price'),
            'sku' => Yii::t('app', 'SKU'),
            'types' => Yii::t('app', 'Product type'),
            'vat' => Yii::t('app', 'VAT'),
            'category_id' => Yii::t('app', 'Category'),
            'unit_id' => Yii::t('app', 'Unit'),
            'manufacturer_id' => Yii::t('app', 'Manufacturer'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id'])->via('division');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManufacturer()
    {
        return $this->hasOne(Manufacturer::className(), ['id' => 'manufacturer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypes()
    {
        return $this->hasMany(ProductType::className(), ['id' => 'type_id'])
            ->viaTable('{{%warehouse_product_type_map}}', ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(ProductUnit::className(), ['id' => 'unit_id']);
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getTypesTitle($separator = ' ') {
        $out = '';
        foreach ($this->productTypes as $key => $type) {
            $out .= $type->name;
            if ($key != (sizeof($this->productTypes) - 1)) {
                $out .= $separator;
            };
        }
        return $out;
    }


    /**
     * Returns mapped list of Product models
     * @return array
     */
    public static function map() {
        return ArrayHelper::map(self::find()->company()->all(), 'id', 'name');
    }

    /**
     * @inheritdoc
     * @return ProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProductQuery(get_called_class());
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param $quantity
     */
    public function writeOff($quantity)
    {
        $this->quantity = $this->quantity - $quantity;

        if ($this->quantity < 0) {
            $this->quantity = 0;
        }
    }

    /**
     * @param $quantity
     */
    public function revertWriteOff($quantity)
    {
        $this->quantity = $this->quantity + $quantity;
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'text' => 'name',
            'name',
            "price",
            'purchase_price',
            'vat',
            'stock_level' => 'quantity',
            'quantity',
            'division_id',
            'unit',
            'product_id' => 'id',
        ];
    }

    public function extraFields()
    {
        return [
            'category'
        ];
    }

    /**
     * removes product
     */
    public function remove()
    {
        $this->status = self::STATUS_DISABLED;
    }

    /**
     * restore product
     */
    public function restore()
    {
        $this->status = self::STATUS_ENABLED;
    }

    public function edit($barcode, $description, $category_id, $division_id, $manufacturer_id,
                         $min_quantity, $name, $quantity, $price, $purchase_price, $sku,
                         $vat, $unit_id)
    {
        $this->barcode          = $barcode;
        $this->description      = $description;
        $this->category_id      = $category_id;
        $this->division_id      = $division_id;
        $this->manufacturer_id  = $manufacturer_id;
        $this->min_quantity     = $min_quantity;
        $this->name             = $name;
        $this->quantity         = $quantity;
        $this->price            = $price;
        $this->purchase_price   = $purchase_price;
        $this->sku              = $sku;
        $this->vat              = $vat;
        $this->unit_id          = $unit_id;
    }

    public static function create($barcode, $description, $category_id, $division_id, $manufacturer_id,
                                  $min_quantity, $name, $quantity, $price, $purchase_price, $sku,
                                  $vat, $unit_id)
    {
        $product = new self();
        $product->barcode           = $barcode;
        $product->description       = $description;
        $product->category_id       = $category_id;
        $product->division_id       = $division_id;
        $product->manufacturer_id   = $manufacturer_id;
        $product->min_quantity      = $min_quantity;
        $product->name              = $name;
        $product->quantity          = $quantity;
        $product->price             = $price;
        $product->purchase_price    = $purchase_price;
        $product->sku               = $sku;
        $product->vat               = $vat;
        $product->unit_id           = $unit_id;

        return $product;
    }

}
