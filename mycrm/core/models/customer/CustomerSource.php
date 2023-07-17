<?php

namespace core\models\customer;

use core\models\company\Company;
use Yii;

/**
 * This is the model class for table "{{%company_customer_sources}}".
 *
 * @property integer           $id
 * @property string            $name
 * @property integer           $type
 * @property integer           $company_id
 *
 * @property Company           $company
 * @property CompanyCustomer[] $companyCustomers
 */
class CustomerSource extends \yii\db\ActiveRecord
{
    const TYPE_DEFAULT = 1;
    const TYPE_DYNAMIC = 2;

    /**
     * @param string $name
     * @param int    $company_id
     *
     * @return CustomerSource
     */
    public static function add(string $name, int $company_id): CustomerSource
    {
        $model = new CustomerSource();
        $model->name = $name;
        $model->type = self::TYPE_DYNAMIC;
        $model->company_id = $company_id;

        return $model;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_customer_sources}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['type', 'company_id'], 'integer'],
            [['name'], 'string', 'max' => 255],

            ['type', 'default', 'value' => self::TYPE_DYNAMIC],

            [
                ['company_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['company_id' => 'id'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'name'       => Yii::t('app', 'Name'),
            'type'       => Yii::t('app', 'Type'),
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
    public function getCompanyCustomers()
    {
        return $this->hasMany(CompanyCustomer::className(),
            ['source_id' => 'id']);
    }

    /**
     * @return int
     */
    public function getCompanyCustomersCount(): int
    {
        return $this->hasMany(
            CompanyCustomer::className(),
            ['source_id' => 'id']
        )->count();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getSources()
    {
        $query = self::find()->where([
            'OR',
            ['type' => self::TYPE_DEFAULT],
            [
                'AND',
                ['type' => self::TYPE_DYNAMIC],
                ['company_id' => Yii::$app->user->identity->company_id],
            ],
        ]);

        return $query->all();
    }

    /**
     * @return array
     */
    public static function map()
    {
        return \yii\helpers\ArrayHelper::map(self::getSources(), 'id', 'name');
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'company_id',
            'count' => function () {
                return 1;
//                return $this->getCompanyCustomersCount();
            },
        ];
    }

    public function beforeDelete()
    {
        if ($this->type === self::TYPE_DEFAULT) {
            return false;
        }

        return parent::beforeDelete();
    }
}
