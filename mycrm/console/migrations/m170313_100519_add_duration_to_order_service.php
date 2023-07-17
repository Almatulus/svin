<?php

use core\models\order\OrderService;
use yii\db\Migration;

class m170313_100519_add_duration_to_order_service extends Migration
{
    /*public function up()
    {

    }

    public function down()
    {
        echo "m170313_100519_add_duration_to_order_service cannot be reverted.\n";

        return false;
    }*/


    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%order_services}}', 'duration', $this->integer()->unsigned());
        echo "--- Start duration writing ---";
        foreach (OrderService::find()->each() as $orderService) {
            $orderService->updateAttributes(['duration' => $orderService->divisionService->average_time]);
        }
        echo "--- End duration writing ---";
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order_services}}', 'duration');
    }

}
