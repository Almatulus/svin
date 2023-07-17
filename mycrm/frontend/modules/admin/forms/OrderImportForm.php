<?php

namespace frontend\modules\admin\forms;

use Yii;
use yii\base\Model;

class OrderImportForm extends Model
{
    public $excelFile;
    public $company_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['excelFile', 'company_id'], 'required'],
            ['excelFile', 'file', 'skipOnEmpty' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'excelFile' => Yii::t('app', 'File'),
            'company_id' => Yii::t('app', 'Company'),
        ];
    }

    public function formName()
    {
        return 'ImportForm';
    }
}
