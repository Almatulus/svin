<?php

namespace frontend\modules\company\forms;

use common\components\Model;
use Yii;

/**
 * @property integer[] $ids
 */
class CompanyPositionMultipleForm extends Model
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
            'ids' => Yii::t('app', 'Position IDs'),
        ];
    }

    public function formName()
    {
        return '';
    }
}
