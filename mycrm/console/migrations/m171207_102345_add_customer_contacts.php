<?php

use yii\db\Migration;

/**
 * Class m171207_102345_add_customer_contacts
 */
class m171207_102345_add_customer_contacts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%customer_contacts}}', [
            'customer_id' => $this->integer()->unsigned()->notNull(),
            'contact_id'  => $this->integer()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'customer_contacts_pkey', '{{%customer_contacts}}', [
            'customer_id',
            'contact_id'
        ]);
        $this->addForeignKey('fk-customer', '{{%customer_contacts}}', 'customer_id',
            '{{%customers}}', 'id');
        $this->addForeignKey('fk-contact', '{{%customer_contacts}}', 'contact_id',
            '{{%customers}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%customer_contacts}}');
    }
}
