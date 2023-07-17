<?php

use yii\db\Migration;

/**
 * Class m180202_043359_add_attributes_to_division_service
 */
class m180202_043359_add_attributes_to_division_service extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%division_services}}', 'notification_delay', $this->integer()->unsigned());

        $this->createTable('{{%delayed_notifications_queue}}', [
            'id'                  => $this->primaryKey(),
            'company_customer_id' => $this->integer()->unsigned()->notNull(),
            'date'                => $this->date()->notNull(),
            'division_service_id' => $this->integer()->notNull(),
            'interval'            => $this->string()->notNull(),
            'status'              => $this->integer()->notNull()->defaultValue(1),
            'created_at'          => $this->dateTime()->notNull(),
            'executed_at'         => $this->dateTime(),
        ]);

        $this->addForeignKey('fk-company_customer', '{{%delayed_notifications_queue}}', 'company_customer_id',
            '{{%company_customers}}', 'id');
        $this->addForeignKey('fk-division-service', '{{%delayed_notifications_queue}}', 'division_service_id',
            '{{%division_services}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%division_services}}', 'notification_delay');

        $this->dropTable('{{%delayed_notifications_queue}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180202_043359_add_attributes_to_division_service cannot be reverted.\n";

        return false;
    }
    */
}
