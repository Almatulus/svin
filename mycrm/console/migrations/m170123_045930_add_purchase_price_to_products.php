<?php

use core\models\order\OrderProduct;
use core\models\warehouse\SaleProduct;
use core\models\warehouse\UsageProduct;
use yii\db\Migration;


class m170123_045930_add_purchase_price_to_products extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170123_045930_add_purchase_price_to_products cannot be reverted.\n";

        return false;
    }*/

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        // цена закупа товара на момент продажи 
        $this->addColumn('{{%order_service_products}}', 'purchase_price', $this->double());
        $this->addColumn('{{%warehouse_sale_product}}', 'purchase_price', $this->double());
        $this->addColumn('{{%warehouse_usage_product}}', 'purchase_price', $this->double());

        $this->addColumn('{{%warehouse_usage_product}}', 'selling_price', $this->double());
        $this->addColumn('{{%order_service_products}}', 'selling_price', $this->double());

        $orderProducts = OrderProduct::find()->all();
        foreach ($orderProducts as $key => $orderProduct) {
            $orderProduct->updateAttributes([
                'purchase_price' => $orderProduct->product->purchase_price,
                'selling_price' => $orderProduct->product->price
            ]);
        }

        $saleProducts = SaleProduct::find()->all();
        foreach ($saleProducts as $key => $saleProduct) {
            $saleProduct->updateAttributes(['purchase_price' => $saleProduct->product->purchase_price]);
        }

        $usageProducts = UsageProduct::find()->all();
        foreach ($usageProducts as $key => $usageProduct) {
            $usageProduct->updateAttributes([
                'purchase_price' => $orderProduct->product->purchase_price,
                'selling_price' => $orderProduct->product->price
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropColumn('{{%warehouse_sale_product}}', 'purchase_price');

        $this->dropColumn('{{%warehouse_usage_product}}', 'selling_price');
        $this->dropColumn('{{%warehouse_usage_product}}', 'purchase_price');
        
        $this->dropColumn('{{%order_service_products}}', 'purchase_price');
        $this->dropColumn('{{%order_service_products}}', 'selling_price');
    }
    
}
