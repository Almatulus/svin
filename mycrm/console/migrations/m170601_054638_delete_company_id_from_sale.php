<?php

use yii\db\Migration;

class m170601_054638_delete_company_id_from_sale extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->dropColumn("{{%warehouse_sale}}", 'company_id');
    }

    public function safeDown()
    {
        $this->addColumn("{{%warehouse_sale}}", 'company_id', $this->integer());
        $this->addForeignKey('fk_sale_company', '{{%warehouse_sale}}', 'company_id', '{{%companies}}', 'id');
    }

}
