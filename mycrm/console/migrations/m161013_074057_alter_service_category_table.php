<?php

use yii\db\Migration;

class m161013_074057_alter_service_category_table extends Migration
{
    public function up()
    {
        $this->update('{{%service_categories}}', ['name' => 'Красота'], ['name' => 'Красота и здоровье']);
        $this->insert('{{%service_categories}}',
            [
                'name' => 'Здоровье',
                'image_id' => 1,
                'parent_category_id' => null,
                'order' => 2,
                'company_id' => null,
                'type' => 1,
            ]
        );
    }

    public function down()
    {
        $this->delete('{{%service_categories}}', ['name' => 'Здоровье']);
        $this->update('{{%service_categories}}', ['name' => 'Красота и здоровье'], ['name' => 'Красота']);
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
