<?php

use yii\db\Migration;

class m161031_073314_update_division_category extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        \core\models\division\Division::updateAll(['category_id' => 2], ['category_id' => null]);
    }

    public function safeDown()
    {
    }
}
