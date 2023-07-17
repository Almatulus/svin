<?php

namespace core\models\warehouse;

use core\models\company\Company;
use core\models\division\Division;
use core\models\finance\CompanyContractor;
use core\models\user\User;
use core\models\warehouse\query\DeliveryQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%warehouse_delivery}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $contractor_id
 * @property integer $creator_id
 * @property string $delivery_date
 * @property integer $division_id
 * @property string $invoice_number
 * @property string $notes
 * @property integer $type
 * @property string $created_at
 * @property string $updated_at
 * @property boolean $is_deleted
 *
 * @property Company $company
 * @property CompanyContractor $contractor
 * @property DeliveryProduct[] $products
 */
class Delivery extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_delivery}}';
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
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['division_id'], 'required'],
            [['company_id', 'contractor_id', 'division_id', 'type', 'creator_id'], 'integer'],
            [['delivery_date', 'created_at', 'updated_at'], 'safe'],
            [['invoice_number', 'notes'], 'string', 'max' => 255],

            [['is_deleted'], 'default', 'value' => false],
            [['is_deleted'], 'boolean'],

            [['creator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['creator_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['contractor_id'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyContractor::className(), 'targetAttribute' => ['contractor_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'company_id' => Yii::t('app', 'Company'),
            'contractor_id' => Yii::t('app', 'Contractor'),
            'delivery_date' => Yii::t('app', 'Delivery date'),
            'division_id' => Yii::t('app', 'Division ID'),
            'invoice_number' => Yii::t('app', 'Invoice number'),
            'notes' => Yii::t('app', 'Comments'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created at'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'creator_id' => Yii::t('app', 'Created By')
        ];
    }

    public function init() {
        if ($this->isNewRecord) {
            $this->delivery_date = date('Y-m-d');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'creator_id']);
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
    public function getContractor()
    {
        return $this->hasOne(CompanyContractor::className(), ['id' => 'contractor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(DeliveryProduct::className(), ['delivery_id' => 'id']);
    }

    public function getProductsTotalCost() {
        return DeliveryProduct::getTotalCost($this->products);
    }

    public static function create($company_id, $creator_id, $contractor_id,
                                  $division_id, $invoice_number, $delivery_date, $notes)
    {
        $model = new self();
        $model->company_id = $company_id;
        $model->creator_id = $creator_id;
        $model->contractor_id = $contractor_id;
        $model->division_id = $division_id;
        $model->invoice_number = $invoice_number;
        $model->delivery_date = $delivery_date;
        $model->notes = $notes;

        return $model;
    }

    public function edit($contractor_id, $division_id, $invoice_number, $delivery_date, $notes)
    {
        $this->contractor_id = $contractor_id;
        $this->division_id = $division_id;
        $this->invoice_number = $invoice_number;
        $this->delivery_date = $delivery_date;
        $this->notes = $notes;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->is_deleted;
    }

    /**
     *
     */
    public function remove()
    {
        $this->is_deleted = true;
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new DeliveryQuery(get_called_class());
    }
}
