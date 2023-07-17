<?php

use yii\db\Migration;

/**
 * Handles the creation of table `customer_subscriptions`.
 */
class m170227_041014_create_customer_subscriptions_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%customer_subscriptions}}', [
            'id' => $this->primaryKey(),
            'company_customer_id' => $this->integer()->unsigned()->notNull(),
            'key' => $this->string()->notNull()->unique(),
            'first_visit' => $this->date(),
            'number_of_persons' => $this->integer()->unsigned(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'quantity' => $this->integer()->unsigned()->notNull(),
            'status' => $this->integer()->unsigned()->defaultValue(0),
            'price' => $this->double()->notNull(),
            'type' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->addForeignKey('fk_subscription_customer', '{{%customer_subscriptions}}', 'company_customer_id', '{{%company_customers}}',
            'id');

        $this->createIndex('customer_subscriptions_customer_id_idx', '{{%customer_subscriptions}}', 'company_customer_id');
        $this->createIndex('customer_subscriptions_start_date_idx', '{{%customer_subscriptions}}', 'start_date');
        $this->createIndex('customer_subscriptions_end_date_idx', '{{%customer_subscriptions}}', 'end_date');
        $this->createIndex('customer_subscriptions_status_idx', '{{%customer_subscriptions}}', 'status');
        $this->createIndex('customer_subscriptions_type_idx', '{{%customer_subscriptions}}', 'type');

        $this->createTable('{{%customer_subscription_services}}', [
            'id' => $this->primaryKey(),
            'subscription_id' => $this->integer()->unsigned()->notNull(),
            'division_service_id' => $this->integer()->unsigned()->notNull()
        ]);

        $this->addForeignKey('fk_subscription_service_subscription', '{{%customer_subscription_services}}', 'subscription_id', '{{%customer_subscriptions}}', 'id');
        $this->addForeignKey('fk_subscription_service_division_service', '{{%customer_subscription_services}}', 'division_service_id', '{{%division_services}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%customer_subscription_services}}');
        $this->dropTable('{{%customer_subscriptions}}');
    }
}
