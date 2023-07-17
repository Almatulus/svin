<?php

namespace core\models\finance;

use core\helpers\customer\CustomerHelper;
use core\models\company\Company;
use core\models\division\Division;
use core\models\finance\query\ContractorQuery;
use Yii;

/**
 * This is the model class for table "crm_company_contractors".
 *
 * @property integer $id
 * @property string $type
 * @property string $name
 * @property integer $division_id
 * @property string $iin
 * @property string $kpp
 * @property string $contacts
 * @property string $phone
 * @property string $email
 * @property string $address
 * @property string $comments
 *
 * @property Company $company
 */
class CompanyContractor extends \yii\db\ActiveRecord
{

    const TYPE_LEGAL = 0;
    const TYPE_PHYSICAL = 1;
    const TYPE_SOLE_TRADER = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_company_contractors';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'division_id', 'type'], 'required'],
            [['division_id', 'type'], 'integer'],
            [['comments'], 'string'],
            ['phone', 'match', 'pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN],
            [['name', 'contacts', 'phone', 'email', 'address'], 'string', 'max' => 255],
            [['iin', 'kpp'], 'string', 'max' => 31]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app','ID'),
            'type' => Yii::t('app','Contractor Type'),
            'name' => Yii::t('app','Name'),
            'division_id' => Yii::t('app','Division ID'),
            'iin' => Yii::t('app','IIN'),
            'kpp' => Yii::t('app','KPP'),
            'contacts' => Yii::t('app','Contractor Contacts'),
            'phone' => Yii::t('app','Phone'),
            'email' => Yii::t('app','Email'),
            'address' => Yii::t('app','Address'),
            'comments' => Yii::t('app','Comments'),
        ];
    }

    public static function getTypeLabels() {
        return [
            self::TYPE_LEGAL => Yii::t('app','Contractor Legal'),
            self::TYPE_PHYSICAL => Yii::t('app','Contractor Physical'),
            self::TYPE_SOLE_TRADER => Yii::t('app','Contractor SoleTrader'),
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
    public function getDivision()
    {
        return $this->hasOne(Division::className(), ['id' => 'division_id']);
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new ContractorQuery(get_called_class());
    }

    public static function map()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->division()->all(), 'id', 'name');
    }
}
