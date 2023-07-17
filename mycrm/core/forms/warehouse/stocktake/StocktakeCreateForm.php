<?php

namespace core\forms\warehouse\stocktake;

use core\models\user\User;
use core\models\warehouse\Category;
use Yii;
use yii\base\Model;


/*
* @property integer $type_of_products
* @property integer $category_id
* @property integer $division_id
* @property string $name
* @property integer $creator_id
* @property string $description
*
*/
class StocktakeCreateForm extends Model
{
    public $type_of_products;
    public $category_id;
    public $name;
    public $division_id;
    public $creator_id;
    public $description;

    /**
     * @inheritdoc
     */
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['creator_id', 'division_id', 'name'], 'required'],
            [['creator_id', 'category_id', 'division_id', 'type_of_products'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],

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
            'creator_id' => Yii::t('app', 'Created By'),
            'category_id' => Yii::t('app', 'Category ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'division_id' => Yii::t('app', 'Division ID'),
            'type_of_products' => Yii::t('app', 'Product type'),
        ];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'StocktakeCreateForm';
    }
}
