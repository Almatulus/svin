<?php

use yii\db\Migration;

/**
 * Handles the creation of table `division_service_product`.
 */
class m161201_055331_create_division_service_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%division_service_products}}', [
            'id' => $this->primaryKey(),
            'division_service_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->double()->notNull(),
        ]);

        $this->addForeignKey('fk_product_service', '{{%division_service_products}}', 'division_service_id', '{{%division_services}}', 'id');
        $this->addForeignKey('fk_product', '{{%division_service_products}}', 'product_id', '{{%warehouse_product}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%division_service_products}}');
    }
}
