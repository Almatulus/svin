<?php

namespace core\models\document;

use core\models\Position;
use Yii;

/**
 * This is the model class for table "crm_document_form_position_map".
 *
 * @property int $document_form_id
 * @property int $position_id
 *
 * @property DocumentForm $documentForm
 * @property Position $position
 */
class DocumentFormPositionMap extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_document_form_position_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_form_id', 'position_id'], 'required'],
            [['document_form_id', 'position_id'], 'default', 'value' => null],
            [['document_form_id', 'position_id'], 'integer'],
            [['document_form_id', 'position_id'], 'unique', 'targetAttribute' => ['document_form_id', 'position_id']],
            [['document_form_id'], 'exist', 'skipOnError' => true, 'targetClass' => DocumentForm::className(), 'targetAttribute' => ['document_form_id' => 'id']],
            [['position_id'], 'exist', 'skipOnError' => true, 'targetClass' => Position::className(), 'targetAttribute' => ['position_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'document_form_id' => 'Document Form ID',
            'position_id' => 'Position ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentForm()
    {
        return $this->hasOne(DocumentForm::className(), ['id' => 'document_form_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosition()
    {
        return $this->hasOne(Position::className(), ['id' => 'position_id']);
    }
}
