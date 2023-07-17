<?php

namespace core\models\warehouse;

use core\models\company\Company;
use core\models\warehouse\query\ManufacturerQuery;
use Yii;

/**
 * This is the model class for table "{{%warehouse_manufacturer}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $company_id
 *
 * @property Company $company
 * @property Product[] $products
 */
class Manufacturer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_manufacturer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            [['company_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
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
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['manufacturer_id' => 'id']);
    }

    public static function map() {
        return \yii\helpers\ArrayHelper::map(self::find()
            ->where(['company_id' => Yii::$app->user->identity->company_id])
            ->all(), 'id', 'name');
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
        return new ManufacturerQuery(get_called_class());
    }
}
