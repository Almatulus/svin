<?php

namespace core\repositories\order;

use core\models\order\OrderPayment;
use core\repositories\exceptions\NotFoundException;

class OrderPaymentRepository
{
    /**
     * @param $id
     * @return OrderPayment
     */
    public function find($id)
    {
        if (!$model = OrderPayment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $order_id
     * @return OrderPayment[]
     */
    public function findByOrder($order_id)
    {
        return OrderPayment::find()->where(['order_id' => $order_id])->indexBy('payment_id')->orderBy('id')->all();
    }

    public function save(OrderPayment $model)
    {
        if ( ! $model->save(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(OrderPayment $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    public function clear($order_id)
    {
        OrderPayment::updateAll(['amount' => 0], ['order_id' => $order_id]);
    }
}