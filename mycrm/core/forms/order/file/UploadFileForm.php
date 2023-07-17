<?php

namespace core\forms\order\file;

use core\models\order\Order;
use Yii;
use yii\base\Model;

class UploadFileForm extends Model
{
    public $order_id;
    public $file;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['order_id', 'file'], 'required'],
            ['order_id', 'integer'],
            ['order_id', 'exist', 'targetClass' => Order::class, 'targetAttribute' => 'id'],
            ['file', 'file']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'file'     => Yii::t('app', 'File')
        ];
    }
}
