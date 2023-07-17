<?php

use yii\db\Migration;

/**
 * Handles the dropping for table `customer_request_keys`.
 */
class m160622_104223_drop_customer_request_keys extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropTable('crm_customer_request_keys');

        $this->addColumn("crm_customers", "key_ios", $this->string());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->createTable('crm_customer_request_keys', [
            'id' => $this->primaryKey(),
            'customer_id' => $this->integer()->notNull(),
            'key' => $this->string(255)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'created_time' => $this->dateTime(),
        ]);

        $this->addForeignKey('fk_customer_request_keys_customer', 'crm_customer_request_keys', 'customer_id', 'crm_customers', 'id');
        $this->dropColumn("crm_customers", "key_ios");
    }

}
