<?php

namespace core\forms\medCard;

use core\models\medCard\MedCardCommentCategory;
use yii\base\Model;

/**
 * @property string  $comment
 * @property integer $comment_template_category_id
 */
class MedCardTabCommentManageForm extends Model
{
    public $comment;
    public $comment_template_category_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['comment_template_category_id'], 'required'],
            [['comment_template_category_id'], 'integer', 'min' => 0],
            ['comment', 'safe'],
            [
                ['comment_template_category_id'],
                'exist',
                'skipOnError'     => false,
                'targetClass'     => MedCardCommentCategory::className(),
                'targetAttribute' => ['comment_template_category_id' => 'id']
            ],
        ];
    }

    public function formName()
    {
        return 'MedCardTabComment';
    }
}
