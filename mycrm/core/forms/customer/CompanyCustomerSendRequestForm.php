<?php

namespace core\forms\customer;

use common\components\Model;
use Yii;

/**
 * @property integer[] $ids
 */
class CompanyCustomerSendRequestForm extends Model
{
    public $ids;
    public $message;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids', 'message'], 'required'],
            [['ids'], 'each', 'rule' => ['integer']],
            ['message', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ids' => Yii::t('app', 'Customer IDs'),
            'message' => Yii::t('app', 'Message'),
        ];
    }

    public function formName()
    {
        return '';
    }
}
