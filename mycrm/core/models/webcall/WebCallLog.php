<?php

namespace core\models\webcall;

use core\models\company\Company;
use Yii;

/**
 * This is the model class for table "{{%company_webcalls_log}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $api_key
 * @property string $username
 * @property string $response
 * @property string $created_time
 * @property string $action
 *
 * @property Company $company
 */
class WebCallLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_webcalls_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'action'], 'required'],
            [['company_id'], 'integer'],
            [['response'], 'string'],
            [['api_key', 'username'], 'string', 'max' => 255],
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
            'company_id' => Yii::t('app', 'Company ID'),
            'api_key' => Yii::t('app', 'Api Key'),
            'username' => Yii::t('app', 'Username'),
            'response' => Yii::t('app', 'Response'),
            'created_time' => Yii::t('app', 'Created Time'),
            'action' => Yii::t('app', 'Action'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
}
