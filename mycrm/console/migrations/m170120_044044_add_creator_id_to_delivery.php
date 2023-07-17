<?php

use yii\db\Migration;

class m170120_044044_add_creator_id_to_delivery extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170120_044044_add_creator_id_to_delivery cannot be reverted.\n";

        return false;
    }*/

    
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%warehouse_delivery}}', 'creator_id', $this->integer()->unsigned());
        $this->addForeignKey('fk_devivery_user', '{{%warehouse_delivery}}', 'creator_id', '{{%users}}', 'id');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%warehouse_delivery}}', 'creator_id');
    }
    
}
