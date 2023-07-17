<?php

use yii\db\Migration;

class m170114_091649_create_table_payments extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%company_payment_log}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'value' => $this->integer()->notNull()->defaultValue(0),
            'currency' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'created_time' => $this->dateTime()->notNull(),
            'confirmed_time' => $this->dateTime(),
            'description' => $this->text(),
            'message' => $this->text(),
        ]);

        $this->addForeignKey('fk_payment_log_company', '{{%company_payment_log}}', 'company_id', '{{%companies}}', 'id');
        $this->createIndex('ix_payment_log_code', '{{%company_payment_log}}', 'company_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%company_payment_log}}');
    }
}
