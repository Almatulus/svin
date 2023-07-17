<?php

namespace core\models\document;

use core\models\customer\CompanyCustomer;
use core\models\Staff;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%documents}}".
 *
 * @property integer $id
 * @property integer $document_form_id
 * @property integer $company_customer_id
 * @property integer $staff_id
 * @property integer $manager_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property DentalCardElement[] $dentalCard
 * @property DocumentValue[] $values
 * @property CompanyCustomer $companyCustomer
 * @property DocumentForm $documentForm
 * @property DocumentService[] $services
 * @property Staff $manager
 * @property Staff $staff
 */
class Document extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%documents}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_form_id', 'company_customer_id', 'created_at', 'updated_at'], 'required'],
            [['document_form_id', 'company_customer_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [
                ['company_customer_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCustomer::className(),
                'targetAttribute' => ['company_customer_id' => 'id']
            ],
            [
                ['document_form_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DocumentForm::className(),
                'targetAttribute' => ['document_form_id' => 'id']
            ],
            [
                ['manager_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id']
            ],
            [
                ['staff_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => Yii::t('app', 'ID'),
            'document_form_id'    => Yii::t('app', 'Document Form ID'),
            'company_customer_id' => Yii::t('app', 'Company Customer ID'),
            'manager_id'          => Yii::t('app', 'Manager ID'),
            'staff_id'            => Yii::t('app', 'Staff ID'),
            'created_at'          => Yii::t('app', 'Created At'),
            'updated_at'          => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return array
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
     * @return \yii\db\ActiveQuery
     */
    public function getValues()
    {
        return $this->hasMany(DocumentValue::className(), ['document_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyCustomer()
    {
        return $this->hasOne(CompanyCustomer::className(), ['id' => 'company_customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDentalCard()
    {
        return $this->hasMany(DentalCardElement::className(), ['document_id' => 'id']);
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
    public function getServices()
    {
        return $this->hasMany(DocumentService::className(), ['document_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(Staff::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_id']);
    }
    /**
     * @inheritdoc
     * @return \core\models\document\query\DocumentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\document\query\DocumentQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'customer_id' => 'company_customer_id',
            'document_form_id',
            'manager_id',
            'staff_id',
            'created_at'
        ];
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [
            'form' => 'documentForm',
            'customer' => 'companyCustomer',
            'dentalCard',
            'manager',
            'services',
            'staff',
            'values'
        ];
    }
}
