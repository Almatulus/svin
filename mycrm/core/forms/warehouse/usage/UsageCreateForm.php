<?php

namespace core\forms\warehouse\usage;

use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\Staff;
use Yii;
use yii\base\Model;

/**
 * Class UsageCreateForm
 * @package core\forms\warehouse\usage
 *
 * @property integer $company_customer_id
 * @property integer $discount
 * @property integer $division_id
 * @property integer $staff_id
 * @property string $updated_at
 * @property string $comments
 */
class UsageCreateForm extends Model
{
    public $company_customer_id;
    public $discount;
    public $division_id;
    public $staff_id;
    public $comments;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['company_customer_id', 'default', 'value' => null],
            ['company_customer_id', 'integer'],
            [
                'company_customer_id',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => CompanyCustomer::class,
                'targetAttribute' => 'id'
            ],

            ['division_id', 'required'],
            ['division_id', 'integer'],
            [
                'division_id',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Division::class,
                'targetAttribute' => 'id'
            ],

            ['staff_id', 'default', 'value' => null],
            ['staff_id', 'integer'],
            [
                'staff_id',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Staff::class,
                'targetAttribute' => 'id'
            ],

            [['discount'], 'default', 'value' => 0],
            [['discount'], 'integer', 'min' => 0, 'max' => 100],

            ['comments', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'company_customer_id' => Yii::t('app', 'Customer'),
            'division_id'         => Yii::t('app', 'Division ID'),
            'staff_id'            => Yii::t('app', 'Staff ID'),
            'comments'            => Yii::t('app', 'Comments')
        ];
    }
}