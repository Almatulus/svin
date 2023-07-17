<?php

namespace core\forms\staff;

use core\models\Staff;
use core\models\StaffReview;
use core\models\customer\Customer;
use Yii;
use yii\base\InvalidCallException;
use yii\base\Model;
use yii\web\ForbiddenHttpException;


/**
 * @property integer $value
 * @property string $comment
 * @property integer $staff_id
 * @property integer $customer_id
 */
class ReviewCreateForm extends Model
{
    public $value;
    public $comment;
    public $staff_id;
    public $customer_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['value', 'staff_id', 'customer_id'], 'required'],

            [['staff_id', 'customer_id'], 'integer'],
            ['value', 'integer', 'min' => 0, 'max' => StaffReview::REVIEW_LIMIT],

            [['comment'], 'string'],

            [['staff_id'], 'exist',
                'skipOnError' => false,
                'targetClass' => Staff::className(),
                'targetAttribute' => ['staff_id' => 'id']],
            [['customer_id'], 'exist',
                'skipOnError' => false,
                'targetClass' => Customer::className(),
                'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    public function formName()
    {
        return '';
    }
}
