<?php

namespace core\models\company;

use core\models\InsuranceCompany;
use core\models\order\Order;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%company_insurances}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $name
 * @property string $description
 * @property string $deleted_time
 * @property integer $insurance_company_id
 *
 * @property Company $company
 */
class Insurance extends \yii\db\ActiveRecord
{
    /**
     * Soft delete
     */
    public function remove()
    {
        $this->deleted_time = date('Y-m-d H:i:s');
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_insurances}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'insurance_company_id'], 'required'],
            [['company_id', 'insurance_company_id'], 'integer'],
            [['description'], 'string'],
            [['deleted_time'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [
                ['company_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['company_id' => 'id']
            ],
            [
                ['insurance_company_id'],
                'exist',
                'skipOnError'     => false,
                'targetClass'     => InsuranceCompany::className(),
                'targetAttribute' => ['insurance_company_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                   => Yii::t('app', 'ID'),
            'company_id'           => Yii::t('app', 'Company ID'),
            'name'                 => Yii::t('app', 'Name'),
            'description'          => Yii::t('app', 'Description'),
            'deleted_time'         => Yii::t('app', 'Deleted Time'),
            'insurance_company_id' => Yii::t('app', 'Insurance Company ID'),
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
    public function getInsuranceCompany()
    {
        return $this->hasOne(InsuranceCompany::className(), ['id' => 'insurance_company_id']);
    }

    /**
     * @param string $from
     * @param string $to
     * @return array
     */
    public static function map($from = "id", $to = "name")
    {
        $models = self::find()
            ->where([
                'deleted_time' => null,
                'company_id'   => Yii::$app->user->identity->company_id
            ])
            ->all();

        return ArrayHelper::map($models, $from, $to);
    }

    public function fields()
    {
        return [
            'id',
            'company_id',
            'name',
            'description',
            'insurance_company_id',
        ];
    }
}
