<?php

namespace core\forms\medCard;

use core\models\division\DivisionService;
use Yii;
use yii\base\Model;

/**
 * @property integer $division_service_id
 * @property integer $price
 * @property integer $quantity
 * @property integer $discount
 */
class MedCardTabServiceForm extends Model
{
    public $division_service_id;
    public $price;
    public $quantity;
    public $discount;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'division_service_id',
                    'price',
                ],
                'required'
            ],
            [
                [
                    'division_service_id',
                    'quantity',
                    'discount',
                ],
                'integer'
            ],
            [['price'], 'number'],
            [
                ['division_service_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => DivisionService::className(),
                'targetAttribute' => ['division_service_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => Yii::t('app', 'ID'),
            'med_card_tab_id'     => Yii::t('app', 'Med Card Tab ID'),
            'division_service_id' => Yii::t('app', 'Division Service ID'),
            'quantity'            => Yii::t('app', 'Quantity'),
            'discount'            => Yii::t('app', 'Discount'),
            'price'               => Yii::t('app', 'Price'),
            'created_user_id'     => Yii::t('app', 'Created User ID'),
            'created_time'        => Yii::t('app', 'Created Time'),
            'deleted_time'        => Yii::t('app', 'Deleted Time'),
        ];
    }

}