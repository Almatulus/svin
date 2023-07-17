<?php

namespace core\forms\customer;

use common\components\Model;
use Yii;

/**
 * @property integer[] $ids
 */
class CompanyCustomerAddCategoriesForm extends Model
{
    public $ids;
    public $category_ids;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ids', 'category_ids'], 'required'],
            [['ids'], 'each', 'rule' => ['integer']],
            [['category_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ids' => Yii::t('app', 'Customer IDs'),
            'category_ids' => Yii::t('app', 'Category IDs'),
        ];
    }

    public function formName()
    {
        return '';
    }
}
