<?php

use yii\db\Migration;

/**
 * Class m180510_050353_add_admin_company_tasks
 */
class m180510_050353_add_admin_company_tasks extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company_tasks}}', [
            'id'         => $this->primaryKey(),
            'type'       => $this->integer()->unsigned()->notNull(),
            'comments'   => $this->text(),
            'start_date' => $this->dateTime()->notNull(),
            'due_date'   => $this->dateTime(),
            'end_date'   => $this->dateTime(),
            'company_id' => $this->integer()->unsigned()->notNull()
        ]);

        $this->addForeignKey('fk_company_tasks_company', '{{%company_tasks}}',
            'company_id', '{{%companies}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%company_tasks}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180510_050353_add_admin_company_tasks cannot be reverted.\n";

        return false;
    }
    */
}
