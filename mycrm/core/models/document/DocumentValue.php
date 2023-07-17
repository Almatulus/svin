<?php

namespace core\models\document;

use Yii;

/**
 * This is the model class for table "{{%document_values}}".
 *
 * @property integer $document_id
 * @property integer $document_form_element_id
 * @property string $value
 *
 * @property DocumentFormElement $documentFormElement
 * @property Document $document
 */
class DocumentValue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_values}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_id', 'document_form_element_id', 'value'], 'required'],
            [['document_id', 'document_form_element_id'], 'integer'],
            [['value'], 'string', 'max' => 255],
            [
                ['document_form_element_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DocumentFormElement::className(),
                'targetAttribute' => ['document_form_element_id' => 'id']
            ],
            [
                ['document_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Document::className(),
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
            'document_id'              => Yii::t('app', 'Document ID'),
            'document_form_element_id' => Yii::t('app', 'Document Form Element ID'),
            'value'                    => Yii::t('app', 'Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentFormElement()
    {
        return $this->hasOne(DocumentFormElement::className(), ['id' => 'document_form_element_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocument()
    {
        return $this->hasOne(Document::className(), ['id' => 'document_id']);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'document_id',
            'document_form_element_id',
            'key'   => function () {
                return $this->documentFormElement->key;
            },
            'value' => function () {
                return $this->documentFormElement->formatValue($this->value);
            }
        ];
    }
}
