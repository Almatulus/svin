<?php

namespace core\forms;

use Yii;
use yii\base\Model;

class ImportForm extends Model
{
    public $excelFile;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['excelFile', 'required'],
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
        ];
    }
}
