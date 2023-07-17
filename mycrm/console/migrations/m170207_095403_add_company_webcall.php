<?php

use yii\db\Migration;

class m170207_095403_add_company_webcall extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable('{{%company_webcalls}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'api_key' => $this->string(),
            'username' => $this->string(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
        ]);

        $this->createTable('{{%company_webcalls_log}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'api_key' => $this->string(),
            'username' => $this->string(),
            'response' => $this->text(),
            'created_time' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('uq_company_webcalls_company', '{{%company_webcalls}}', 'company_id', true);
        $this->addForeignKey('fk_company_webcalls_company', '{{%company_webcalls}}', 'company_id', '{{%companies}}', 'id');
        $this->addForeignKey('fk_company_webcalls_log_company', '{{%company_webcalls_log}}', 'company_id', '{{%companies}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_company_webcall_company', '{{%company_webcalls}}');
        $this->dropTable('{{%company_webcalls}}');
    }
}
