<?php

namespace core\models;

use core\models\company\Insurance;
use core\models\division\DivisionService;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%insurance_companies}}".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Insurance[] $companyInsurances
 * @property DivisionService[] $divisionServices
 */
class InsuranceCompany extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%insurance_companies}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'   => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyInsurances()
    {
        return $this->hasMany(Insurance::className(), ['insurance_company_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDivisionServices()
    {
        return $this->hasMany(DivisionService::className(), ['insurance_company_id' => 'id']);
    }

    /**
     * @return \core\models\query\InsuranceCompanyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\query\InsuranceCompanyQuery(get_called_class());
    }

    /**
     * @param bool $onlyEnabled
     * @param string $from
     * @param string $to
     * @return array
     */
    public static function map($onlyEnabled = true, $from = "id", $to = "name")
    {
        $query = self::find();
        if ($onlyEnabled) {
            $query->enabled();
        }
        return ArrayHelper::map($query->all(), $from, $to);
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'is_enabled' => function () {
                $company_id = Yii::$app->user->identity->company_id;
                return $this->getCompanyInsurances()->andWhere(['company_id' => $company_id])->exists();
            }
        ];
    }
}
