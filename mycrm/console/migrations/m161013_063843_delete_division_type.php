<?php

use yii\db\Migration;

class m161013_063843_delete_division_type extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_type', 'crm_divisions');
        $this->dropColumn("crm_divisions", "type_id");
        $this->dropTable('crm_division_types');

        $this->addColumn('crm_divisions', 'category_id', $this->integer());
        $this->addForeignKey('fk_category', 'crm_divisions', 'category_id', 'crm_service_categories', 'id');

        $this->update('{{%divisions}}', ['category_id' => 2]);
    }

    public function down()
    {
        $this->dropForeignKey('fk_category', 'crm_divisions');
        $this->dropColumn('crm_divisions', 'category_id');

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
