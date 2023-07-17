<?php

use yii\db\Migration;

/**
 * Class m180215_100755_unlink_order_products_with_services
 */
class m180215_100755_unlink_order_products_with_services extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%order_service_products}}', 'order_id', $this->integer()->unsigned());

        $this->execute('UPDATE {{%order_service_products}} AS op 
            SET order_id = os.order_id
            FROM {{%order_services}} AS os
            WHERE op.order_service_id = os.id
        ');

        $this->dropColumn('{{%order_service_products}}', 'order_service_id');

        $this->execute("ALTER TABLE {{%order_service_products}} ALTER COLUMN order_id SET NOT NULL");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%order_service_products}}', 'order_service_id', $this->integer()->unsigned());

        $this->execute('UPDATE {{%order_service_products}} AS op
            SET order_service_id = os.id
            FROM {{%order_services}} AS os
            WHERE op.order_id = os.order_id
        ');

//        $this->execute("ALTER TABLE {{%order_service_products}} ALTER COLUMN order_service_id SET NOT NULL");

        $this->dropColumn('{{%order_service_products}}', 'order_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180215_100755_unlink_order_products_with_services cannot be reverted.\n";

        return false;
    }
    */
}
