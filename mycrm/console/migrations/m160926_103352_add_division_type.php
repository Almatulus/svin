<?php

use yii\db\Migration;

class m160926_103352_add_division_type extends Migration
{
    public function up()
    {
        $this->createTable('crm_division_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()
        ]);

        $this->insert('crm_division_types', ['name' => "Красота"]);
        $this->insert('crm_division_types', ['name' => "Здоровье"]);
        $this->insert('crm_division_types', ['name' => "Спорт"]);
        $this->insert('crm_division_types', ['name' => "Автосервис"]);
        $this->insert('crm_division_types', ['name' => "Бизнес"]);

        $this->addColumn("crm_divisions", "type_id", $this->integer());
        $this->addForeignKey('fk_type', 'crm_divisions', 'type_id', 'crm_division_types', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_type', 'crm_divisions');
        $this->dropColumn("crm_divisions", "type_id");
        $this->dropTable('crm_division_types');
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
