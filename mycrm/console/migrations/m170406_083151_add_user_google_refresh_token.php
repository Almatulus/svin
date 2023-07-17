<?php

use yii\db\Migration;

class m170406_083151_add_user_google_refresh_token extends Migration
{
    /*
    public function up()
    {

    }

    public function down()
    {
        echo "m170406_083151_add_user_google_refresh_token cannot be reverted.\n";

        return false;
    }
    */


    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn("{{%users}}", "google_refresh_token", $this->string());
        $this->addColumn("{{%orders}}", "google_event_id", $this->string());
    }

    public function safeDown()
    {
        $this->dropColumn("{{%users}}", "google_refresh_token");
        $this->dropColumn("{{%orders}}", "google_event_id");
    }

}
