<?php

use yii\db\Migration;

/**
 * Class m171212_060602_create_company_notifications
 */
class m171212_060602_create_company_notifications extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%company_notifications}}', [
            'company_id' => $this->integer()->unsigned()->notNull(),
            'type'       => $this->integer()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'company_notifications_pkey', '{{%company_notifications}}', [
            'company_id',
            'type'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%company_notifications}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171212_060602_create_company_notifications cannot be reverted.\n";

        return false;
    }
    */
}
