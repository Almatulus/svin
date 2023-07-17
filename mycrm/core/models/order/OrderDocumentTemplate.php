<?php

namespace core\models\order;

use core\models\ServiceCategory;
use Yii;

/**
 * This is the model class for table "{{%order_document_templates}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $filename
 * @property integer $category_id
 * @property integer $company_id
 * @property string $path
 *
 * @property ServiceCategory $category
 */
class OrderDocumentTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_document_templates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'filename', 'category_id'], 'required'],
            [['category_id', 'company_id'], 'integer'],
            [['name', 'filename', 'path'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ServiceCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
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
            'filename' => Yii::t('app', 'Filename'),
            'category_id' => Yii::t('app', 'Category ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ServiceCategory::className(), ['id' => 'category_id']);
    }
}
