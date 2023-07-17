<?php

use yii\db\Migration;

/**
 * Class m180705_110152_create_webcall_accounts
 */
class m180705_110152_create_webcall_accounts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company_webcall_accounts}}', [
            'id'          => $this->primaryKey(),
            'division_id' => $this->integer()->unsigned()->notNull(),
            'name'        => $this->string()->notNull(),
            'email'       => $this->string()->notNull(),
            'created_at'  => $this->dateTime()->notNull(),
            'updated_at'  => $this->dateTime()->notNull()
        ]);

        $this->addForeignKey('fk_company_webcall_accounts', '{{%company_webcall_accounts}}', 'division_id',
            '{{%divisions}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%company_webcall_accounts}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180705_110152_create_webcall_accounts cannot be reverted.\n";

        return false;
    }
    */
}
