<?php

namespace core\forms\warehouse\product;

use core\models\division\Division;
use core\models\warehouse\Category;
use core\models\warehouse\Manufacturer;
use core\models\warehouse\ProductType;
use core\models\warehouse\ProductUnit;
use Yii;
use yii\base\Model;

class ProductCreateForm extends Model
{
    public $barcode;
    public $description;
    public $category_id;
    public $division_id;
    public $manufacturer_id;
    public $min_quantity;
    public $name;
    public $quantity;
    public $price;
    public $purchase_price;
    public $sku;
    public $types;
    public $vat;
    public $unit_id;
    protected $product;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->types = ProductType::find()->select('id')->column();
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['barcode', 'string', 'max' => 255],

            ['description', 'string'],

            ['category_id', 'integer'],
            ['category_id', 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],

            ['division_id', 'required'],
            ['division_id', 'integer'],
            ['division_id', 'exist', 'targetClass' => Division::class, 'targetAttribute' => 'id'],

            ['manufacturer_id', 'integer'],
            ['manufacturer_id', 'exist', 'targetClass' => Manufacturer::class, 'targetAttribute' => 'id'],

            ['name', 'required'],
            ['name', 'string', 'max' => 255],
//            [
//                'name',
//                'unique',
//                'message'         => 'Товар с таким наименованием для данного заведения уже существует',
//                'filter'          => function (ProductQuery $query) {
//                    if ($this->product) {
//                        $query->andFilterWhere(['<>', 'id', $this->product->id]);
//                    }
//                },
//                'targetClass'     => Product::class,
//                'targetAttribute' => ['name', 'division_id'],
//            ],

            ['quantity', 'required'],
            [['quantity', 'min_quantity'], 'integer', 'min' => 0],

            ['price', 'number'],
            ['price', 'default', 'value' => 0],

            ['purchase_price', 'number'],
            ['purchase_price', 'default', 'value' => 0],

            ['sku', 'string', 'max' => 255],

            ['types', 'default', 'value' => []],
            ['types', 'each', 'rule' => ['integer']],
            [
                'types',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => ProductType::className(),
                'targetAttribute' => 'id',
                'allowArray'      => true
            ],

            ['vat', 'default', 'value' => 0],
            ['vat', 'integer', 'min' => 0, 'max' => 100],

            ['unit_id', 'required'],
            ['unit_id', 'integer'],
            ['unit_id', 'exist', 'targetClass' => ProductUnit::class, 'targetAttribute' => 'id'],
            
            [['barcode', 'description', 'name', 'sku'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('app', 'ID'),
            'barcode'         => Yii::t('app', 'Barcode'),
            'description'     => Yii::t('app', 'Description'),
            'division_id'     => Yii::t('app', 'Division ID'),
            'quantity'        => Yii::t('app', 'Stock level'),
            'min_quantity'    => Yii::t('app', 'Minimum stock level'),
            'name'            => Yii::t('app', 'Name'),
            'price'           => Yii::t('app', 'Selling price'),
            'purchase_price'  => Yii::t('app', 'Purchase price'),
            'sku'             => Yii::t('app', 'SKU'),
            'types'           => Yii::t('app', 'Product type'),
            'vat'             => Yii::t('app', 'VAT'),
            'category_id'     => Yii::t('app', 'Category'),
            'unit_id'         => Yii::t('app', 'Unit'),
            'manufacturer_id' => Yii::t('app', 'Manufacturer'),
        ];
    }

}