<?php

namespace core\models\warehouse;

use core\models\company\Company;
use core\models\division\Division;
use core\models\user\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%warehouse_stocktake}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $creator_id
 * @property integer $category_id
 * @property string $description
 * @property integer $division_id
 * @property string $name
 * @property integer $status
 * @property integer $type_of_products
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property User $creator
 * @property Category $category
 * @property StocktakeProduct[] $products
 * @property StocktakeProduct[] $changedProducts
 */
class Stocktake extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_CORRECTED = 2;
    const STATUS_COMPLETED = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_stocktake}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['creator_id', 'division_id', 'name'], 'required'],
            [['company_id', 'creator_id', 'category_id', 'division_id', 'status', 'type_of_products'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'description'], 'string', 'max' => 255],

            ['company_id', 'default', 'value' => Yii::$app->user->identity->company_id],

            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['creator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['creator_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company_id' => Yii::t('app', 'Company ID'),
            'creator_id' => Yii::t('app', 'Created By'),
            'category_id' => Yii::t('app', 'Category ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'division_id' => Yii::t('app', 'Division ID'),
            'status' => Yii::t('app', 'Status'),
            'type_of_products' => Yii::t('app', 'Product type'),
            'created_at' => Yii::t('app', 'Stocktake date'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'accurateProductsCount' => Yii::t('app', 'No. of products with accurate stock level'),
            'productsWithShortageCount' => Yii::t('app', 'Number of products with a shortage'),
            'productsWithSurplusCount' => Yii::t('app', 'Number of products with a surplus'),
            'numberOfProducts' => Yii::t('app', 'Number of products'),
            'title' => Yii::t('app', 'Stocktake date and name')
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()')
            ],
        ];
    }

    /**
    * @inheritdoc
    */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->creator_id = Yii::$app->user->id;
        }
    }

    /**
    * @inheritdoc
    */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            StocktakeProduct::deleteAll(['stocktake_id' => $this->id]);
            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'creator_id']);
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
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(StocktakeProduct::className(), ['stocktake_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \core\models\warehouse\query\StocktakeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\warehouse\query\StocktakeQuery(get_called_class());
    }

    public static function create($type_of_products, $category_id, $name, $division_id,
                                  $company_id, $creator_id, $description)
    {
        $model = new self();
        $model->type_of_products = $type_of_products;
        $model->category_id = $category_id;
        $model->name = $name;
        $model->division_id = $division_id;
        $model->company_id = $company_id;
        $model->creator_id = $creator_id;
        $model->description = $description;
        $model->status = self::STATUS_NEW;

        return $model;
    }

    public function getChangedProducts()
    {
        return $this->getProducts()->where('recorded_stock_level != actual_stock_level')->all();
    }

    public function getNumberOfProducts()
    {
        return sizeof($this->products);
    }

    public function getAccurateProductsCount()
    {
        $counter = 0;
        foreach ($this->products as $key => $product) {
            if ($product->balance == 0) {
                $counter++;
            }
        }
        return $counter;
    }

    public function getProductsWithShortageCount()
    {
        $counter = 0;
        foreach ($this->products as $key => $product) {
            if ($product->balance < 0) {
                $counter++;
            }
        }
        return $counter;
    }

    public function getProductsWithSurplusCount()
    {
        $counter = 0;
        foreach ($this->products as $key => $product) {
            if ($product->balance > 0) {
                $counter++;
            }
        }
        return $counter;
    }

    public function getTitle()
    {
        return $this->name . ", " . Yii::$app->formatter->asDate($this->created_at, "php:Y-m-d");
    }

    public function getLink()
    {
        if ($this->status == self::STATUS_NEW) {
            return ['edit-products', 'id' => $this->id];
        } else if ($this->status == self::STATUS_CORRECTED) {
            return ['summary', 'id' => $this->id];
        }
            return ['view', 'id' => $this->id];
    }

    public function hasProducts()
    {
        return count($this->products) > 0 ? true : false;
    }

    public function correct()
    {
        $this->status = self::STATUS_CORRECTED;
    }

    public function complete()
    {
        $this->status = self::STATUS_COMPLETED;
    }
}
