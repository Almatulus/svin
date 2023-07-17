<?php

namespace core\models\document;

use core\models\division\DivisionService;
use Yii;

/**
 * This is the model class for table "{{%document_services}}".
 *
 * @property int $document_id
 * @property int $service_id
 * @property int $quantity
 * @property int $price
 * @property int $discount
 *
 * @property DivisionService $service
 * @property Document $document
 */
class DocumentService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_services}}';
    }

    /**
     * @inheritdoc
     * @return \core\models\document\query\DocumentServiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\document\query\DocumentServiceQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_id', 'service_id', 'quantity', 'price', 'discount'], 'required'],
            [['document_id', 'service_id'], 'default', 'value' => null],
            [['price', 'discount'], 'default', 'value' => 0],
            [['quantity'], 'default', 'value' => 1],
            [['document_id', 'service_id', 'quantity', 'price', 'discount'], 'integer'],
            [['document_id', 'service_id'], 'unique', 'targetAttribute' => ['document_id', 'service_id']],
            [
                ['service_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DivisionService::class,
                'targetAttribute' => ['service_id' => 'id']
            ],
            [
                ['document_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Document::class,
                'targetAttribute' => ['document_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'document_id' => Yii::t('app', 'Document ID'),
            'service_id'  => Yii::t('app', 'Service ID'),
            'quantity'    => Yii::t('app', 'Quantity'),
            'price'       => Yii::t('app', 'Price'),
            'discount'    => Yii::t('app', 'Discount'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(DivisionService::class, ['id' => 'service_id']);
    }

    public function getTotalPrice()
    {
        return intval($this->price * (100 - $this->discount) / 100);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'document_id']);
    }

    public function extraFields()
    {
        return [
            'service'
        ];
    }
}
