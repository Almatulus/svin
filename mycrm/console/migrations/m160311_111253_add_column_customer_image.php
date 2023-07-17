<?php

use yii\db\Migration;

class m160311_111253_add_column_customer_image extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("crm_customers", "image_id", $this->integer());
        $this->addForeignKey("fk_customers_image", "crm_customers", "image_id", "crm_images", "id");
    }

    public function safeDown()
    {
        $this->dropColumn("crm_customers", "image_id");
    }
}
