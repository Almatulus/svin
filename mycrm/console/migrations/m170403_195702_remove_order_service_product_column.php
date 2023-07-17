<?php

use core\models\order\OrderProduct;
use yii\db\Migration;

class m170403_195702_remove_order_service_product_column extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%order_service_products}}', 'order_id');
    }

    public function safeDown()
    {
        $this->addColumn('{{%order_service_products}}', 'order_id', $this->integer());
        foreach (OrderProduct::find()->each() as $product) {
            /* @var OrderProduct $product */
            $product->order_id = $product->orderService->order_id;
            if (!$product->save()) {
                throw new Exception('Product save error');
            }
        }
        $this->addForeignKey('fk_order_service_products_order', '{{%order_service_products}}', 'order_id', '{{%orders}}', 'id');
    }
}
