<?php

use yii\db\Migration;

class m160303_052903_create_crm_order_history extends Migration
{
    public function up()
    {
        $this->createTable('crm_order_history', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'created_time' => $this->dateTime()->notNull()->defaultValue("now()"),
            "status" => $this->integer(),
            "customer_name" => $this->string(),
            "customer_phone" => $this->string(),
            "customer_comment" => $this->text(),
            "datetime" => $this->dateTime(),
            "service_name" => $this->string(),
            "staff_name" => $this->string(),
            "staff_position" => $this->string(),
            "type" => $this->integer(),
            "discount" => $this->integer(),
        ]);
    }

    public function down()
    {
        $this->dropTable('crm_order_history');
    }
}
