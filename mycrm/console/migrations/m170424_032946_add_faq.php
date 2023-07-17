<?php

use yii\db\Migration;

class m170424_032946_add_faq extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m170424_032946_add_faq cannot be reverted.\n";

    //     return false;
    // }


    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable("{{%faq_item}}", [
            'id' => $this->primaryKey(),
            'question' => $this->string()->notNull(),
            'answer' => $this->text()->notNull(),
            "created_at" => $this->dateTime(),
            "updated_at" => $this->dateTime()
        ]);
    }

    public function safeDown()
    {
        $this->dropTable("{{%faq_item}}");
    }

}
