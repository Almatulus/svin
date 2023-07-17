<?php

use yii\db\Migration;

class m160307_174744_rename_review_table extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->renameTable("crm_reviews", "crm_staff_reviews");
    }

    public function safeDown()
    {
        $this->renameTable("crm_staff_reviews", "crm_reviews");
    }
}
