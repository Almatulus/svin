<?php

namespace core\models\company;

use core\models\company\Company;
use core\models\order\Order;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%company_referrers}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $company_id
 *
 * @property Company $company
 * @property Order[] $orders
 */
class Referrer extends \yii\db\ActiveRecord
{
    public static function add($name, $company_id): Referrer
    {
        $model              = new Referrer();
        $model->name        = $name;
        $model->company_id = $company_id;

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id' => 'id',
            'name' => 'name',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_referrers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'company_id'], 'required'],
            [['company_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name', 'company_id'], 'unique', 'targetAttribute' => ['name', 'company_id'], 'message' => 'The combination of Name and Company ID has already been taken.'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
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
    public function getOrder()
    {
        return $this->hasMany(Order::className(), ['referrer_id' => 'id']);
    }

    /**
     * @return array
     */
    public static function map()
    {
        $models = self::find()
            ->where([
                'company_id'   => Yii::$app->user->identity->company_id
            ])
            ->all();

        return ArrayHelper::map($models, 'id', 'name');
    }
}
