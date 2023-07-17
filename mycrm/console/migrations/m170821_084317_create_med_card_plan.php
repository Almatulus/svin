<?php

use core\models\user\User;
use core\models\division\DivisionService;
use core\models\medCard\MedCardTab;
use yii\db\Migration;

class m170821_084317_create_med_card_plan extends Migration
{
    /**
     *
     */
    public function safeUp()
    {
        $this->createTable('{{%med_card_tab_services}}', [
            'id'                  => $this->primaryKey(),
            'med_card_tab_id'    => $this->integer()->unsigned()->notNull(),
            'division_service_id' => $this->integer()->unsigned()->notNull(),
            'quantity'            =>
                $this->integer()->unsigned()->notNull()->defaultValue(1),
            'discount'            =>
                $this->integer()->unsigned()->notNull()->defaultValue(0),
            'price'               => $this->money()->notNull(),
            'created_user_id' => $this->integer()->unsigned()->notNull(),
            'created_time'    =>
                $this->dateTime()->notNull()->defaultExpression('now()'),
            'deleted_time'        => $this->dateTime(),
        ]);

        $this->addForeignKey(
            'fk_med_card_tab_services_med_card_tab',
            '{{%med_card_tab_services}}',
            'med_card_tab_id',
            MedCardTab::tableName(),
            'id'
        );

        $this->addForeignKey(
            'fk_med_card_tab_services_division_service',
            '{{%med_card_tab_services}}',
            'division_service_id',
            DivisionService::tableName(),
            'id'
        );

        $this->addForeignKey(
            'fk_med_card_tab_services_created_user',
            '{{%med_card_tab_services}}',
            'created_user_id',
            User::tableName(),
            'id'
        );

        $this->createIndex(
            'uq_med_card_tab_services_med_card_tab_division_service',
            '{{%med_card_tab_services}}',
            ['med_card_tab_id', 'division_service_id'],
            true
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%med_card_tab_services}}');
    }
}
