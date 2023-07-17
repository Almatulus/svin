<?php

namespace core\models\document;

use core\models\company\CompanyPosition;
use Yii;

/**
 * This is the model class for table "crm_document_form_company_position_map".
 *
 * @property int $document_form_id
 * @property int $company_position_id
 *
 * @property CompanyPosition $companyPosition
 * @property DocumentForm $documentForm
 */
class DocumentFormCompanyPositionMap extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_document_form_company_position_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_form_id', 'company_position_id'], 'required'],
            [['document_form_id', 'company_position_id'], 'default', 'value' => null],
            [['document_form_id', 'company_position_id'], 'integer'],
            [['document_form_id', 'company_position_id'], 'unique', 'targetAttribute' => ['document_form_id', 'company_position_id']],
            [['company_position_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyPosition::className(), 'targetAttribute' => ['company_position_id' => 'id']],
            [['document_form_id'], 'exist', 'skipOnError' => true, 'targetClass' => DocumentForm::className(), 'targetAttribute' => ['document_form_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'document_form_id' => 'Document Form ID',
            'company_position_id' => 'Company Position ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyPosition()
    {
        return $this->hasOne(CompanyPosition::className(), ['id' => 'company_position_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentForm()
    {
        return $this->hasOne(DocumentForm::className(), ['id' => 'document_form_id']);
    }
}
