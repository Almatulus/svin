<?php

use core\models\customer\CustomerCategory;
use yii\db\Migration;

class m160906_040413_alter_customer_categories extends Migration
{
    public function up()
    {
        $this->addColumn(CustomerCategory::tableName(), 'discount', $this->smallInteger());
    }

    public function down()
    {
        $this->dropColumn(CustomerCategory::tableName(), 'discount');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
