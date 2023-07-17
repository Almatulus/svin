<?php

namespace core\forms\order;

use core\models\order\Order;
use core\models\order\OrderDocumentTemplate;
use yii\base\Model;

/**
 * @property $order_id;
 * @property $template_id;
 */
class OrderDocumentForm extends Model
{
    public $order_id;
    public $template_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['order_id', 'template_id'], 'required'],
            [['order_id', 'template_id'], 'integer'],
            [['order_id'], 'exist', 'skipOnError' => false, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['template_id'], 'exist', 'skipOnError' => false, 'targetClass' => OrderDocumentTemplate::className(), 'targetAttribute' => ['template_id' => 'id']]
            // [['order_id', 'template_id'], 'unique', 'targetClass' => OrderDocument::className(), 'targetAttribute' => ['order_id', 'template_id']]
        ];
    }

    public function formName()
    {
        return '';
    }
}
