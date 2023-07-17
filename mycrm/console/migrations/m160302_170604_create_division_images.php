<?php

use yii\db\Migration;

class m160302_170604_create_division_images extends Migration
{
    public function up()
    {
        $this->createTable('crm_division_images', [
            'id' => $this->primaryKey(),
            'division_id' => $this->integer()->notNull(),
            'image_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey("fk_division_images_division", "crm_division_images", "division_id", "crm_divisions", "id");
        $this->addForeignKey("fk_division_images_image", "crm_division_images", "image_id", "crm_images", "id");
    }

    public function down()
    {
        $this->dropTable('crm_division_images');
    }
}
