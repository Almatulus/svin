<?php

namespace core\models\warehouse;

use core\models\company\Company;
use core\models\warehouse\query\CategoryQuery;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%warehouse_category}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property integer $company_id
 *
 * @property Company $company
 * @property Category $parent
 * @property Category[] $categories
 * @property Product[] $products
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            [['parent_id', '!company_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [
                ['!company_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Company::className(),
                'targetAttribute' => ['company_id' => 'id']
            ],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['parent_id' => 'id']],
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
            'parent_id' => Yii::t('app', 'Parent category'),
            'company_id' => Yii::t('app', 'Company ID'),
        ];
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
    public function getParent()
    {
        return $this->hasOne(Category::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['category_id' => 'id']);
    }

    /**
     * @return Category[]
     */
    public static function getCompanyCategories() {
        return self::find()->where(['company_id' => Yii::$app->user->identity->company_id])->all();
    }

    /**
     * Returns mapped list of Product models
     */
    public static function map()
    {
        return ArrayHelper::map(self::getCompanyCategories(), 'id', 'name');
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            "id",
            "company_id",
            "name",
            "parent_id"
        ];
    }

    public function extraFields()
    {
        return [
            "products",
        ];
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->unlinkAll('products');
            return true;
        }
        return true;
    }

    public static function create($name, $company_id)
    {
        $model = new self();
        $model->name = $name;
        $model->company_id = $company_id;

        return $model;
    }

    public function edit($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new CategoryQuery(get_called_class());
    }
}
