<?php

namespace core\models\document;

use Yii;

/**
 * This is the model class for table "{{%document_form_group}}".
 *
 * @property integer $id
 * @property integer $order
 * @property string $label
 * @property integer $document_form_id
 *
 * @property DocumentFormElement[] $documentFormElements
 * @property DocumentForm $documentForm
 */
class DocumentFormGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_form_groups}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order', 'document_form_id'], 'integer'],
            [['label', 'document_form_id'], 'required'],
            [['label'], 'string', 'max' => 255],
            [
                ['document_form_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DocumentForm::className(),
                'targetAttribute' => ['document_form_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => Yii::t('app', 'ID'),
            'order'            => Yii::t('app', 'Order'),
            'label'            => Yii::t('app', 'Label'),
            'document_form_id' => Yii::t('app', 'Document Form ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentFormElements()
    {
        return $this->hasMany(DocumentFormElement::className(), ['document_form_group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentForm()
    {
        return $this->hasOne(DocumentForm::className(), ['id' => 'document_form_id']);
    }
}
