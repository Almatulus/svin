<?php

namespace core\forms\order;

use core\models\division\DivisionService;
use yii\base\Model;

/**
 * @property integer $discount
 * @property integer $division_service_id
 * @property integer $price
 * @property integer $duration
 * @property integer $quantity
 * @property integer $order_service_id
 */
class OrderServiceManageForm extends Model
{
    public $discount;
    public $division_service_id;
    public $price;
    public $duration;
    public $quantity;
    public $order_service_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['division_service_id', 'quantity'], 'required'],
            [['price', 'duration', 'discount', 'division_service_id'], 'integer', 'min' => 0],
            [['quantity'], 'integer', 'min' => 1],
            ['order_service_id', 'safe'],
            [['division_service_id'], 'exist', 'skipOnError' => false, 'targetClass' => DivisionService::className(), 'targetAttribute' => ['division_service_id' => 'id']],
        ];
    }

    public function formName()
    {
        return 'OrderService';
    }
}
