<?php

namespace core\forms\customer;

use common\components\Model;
use Yii;

/**
 * @property integer[] $ids
 */
class CompanyCustomerMultipleForm extends Model
{
    public $ids;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ids' => Yii::t('app', 'Customer IDs'),
        ];
    }

    public function formName()
    {
        return '';
    }
}
