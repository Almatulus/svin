<?php

use yii\db\Migration;

class m170503_040342_link_order_with_usage extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("{{%warehouse_usage}}", "status", $this->integer()->notNull()->defaultValue(1));

        $this->createTable("{{%order_usage}}", [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'usage_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey("fk_order_usage", "{{%order_usage}}", "usage_id",
            "{{%warehouse_usage}}", "id");
        $this->addForeignKey("fk_usage_order", "{{%order_usage}}", "order_id",
            "{{%orders}}", "id");

        $this->createIndex('crm_order_usage_order_id_idx', '{{%order_usage}}', 'order_id');
        $this->createIndex('crm_order_usage_usage_id_idx', '{{%order_usage}}', 'usage_id');
    }

    public function safeDown()
    {
        $this->dropTable("{{%order_usage}}");
        $this->dropColumn("{{%warehouse_usage}}", "status");
    }

}
