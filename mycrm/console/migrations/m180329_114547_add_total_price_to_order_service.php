<?php

use yii\db\Migration;

/**
 * Class m180329_114547_add_total_price_to_order_service
 */
class m180329_114547_add_total_price_to_order_service extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_services}}', 'total_price', $this->integer()->unsigned()->defaultValue(0));

        $this->execute("
            UPDATE {{%order_services}}
            SET total_price=price * (100 - discount) / 100
        ");

        $this->createIndex('order_services_order_id_idx', '{{%order_services}}', 'order_id');
        $this->createIndex('order_services_division_service_id_idx', '{{%order_services}}', 'division_service_id');
        $this->createIndex('order_products_order_id_idx', '{{%order_service_products}}', 'order_id');
        $this->createIndex('order_products_product_id_idx', '{{%order_service_products}}', 'product_id');

        $this->createIndex('service_division_map_division_service_id_idx', '{{%service_division_map}}',
            'division_service_id');
        $this->createIndex('service_division_map_division_id_idx', '{{%service_division_map}}', 'division_id');

        $this->createIndex('division_services_map_division_service_id_idx', '{{%division_services_map}}',
            'division_service_id');
        $this->createIndex('division_services_map_category_id_idx', '{{%division_services_map}}', 'category_id');

        $this->createIndex('orders_status_idx', '{{%orders}}', 'status');
        $this->createIndex('orders_status_datetime_idx', '{{%orders}}', 'datetime');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('orders_status_idx', '{{%orders}}');
        $this->dropIndex('orders_status_datetime_idx', '{{%orders}}');

        $this->dropIndex('service_division_map_division_service_id_idx', '{{%service_division_map}}');
        $this->dropIndex('service_division_map_division_id_idx', '{{%service_division_map}}');

        $this->dropIndex('division_services_map_division_service_id_idx', '{{%division_services_map}}');
        $this->dropIndex('division_services_map_category_id_idx', '{{%division_services_map}}');

        $this->dropIndex('order_services_order_id_idx', '{{%order_services}}');
        $this->dropIndex('order_services_division_service_id_idx', '{{%order_services}}');
        $this->dropIndex('order_products_order_id_idx', '{{%order_service_products}}');
        $this->dropIndex('order_products_product_id_idx', '{{%order_service_products}}');

        $this->dropColumn('{{%order_services}}', 'total_price');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180329_114547_add_total_price_to_order_service cannot be reverted.\n";

        return false;
    }
    */
}
