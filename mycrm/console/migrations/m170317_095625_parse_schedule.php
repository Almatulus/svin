<?php

use core\models\StaffSchedule;
use yii\db\Migration;

class m170317_095625_parse_schedule extends Migration
{
    // public function up()
    // {

    // }

    // public function down()
    // {
    //     echo "m170317_095625_parse_schedule cannot be reverted.\n";

    //     return false;
    // }


    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $day = date('w');
        $week_start = date('Y-m-d', strtotime('-'.$day.' days'));
        $new_interval = 5;

        $scheduleItems = StaffSchedule::find()->where([">=", "datetime", $week_start]);
        foreach ($scheduleItems->each() as $scheduleItem) {
            if ($scheduleItem->elapsed_time == 10) {
                echo "Start parse schedule with id {$scheduleItem->id}\n";

                $scheduleItem->updateAttributes(['elapsed_time' => $new_interval]);

                $datetime = new DateTime($scheduleItem->datetime);
                $datetime->modify("+{$new_interval} minutes");

                $staffSchedule = new StaffSchedule();
                $staffSchedule->staff_id = $scheduleItem->staff_id;
                $staffSchedule->datetime = $datetime->format("Y-m-d H:i:s");
                if ($scheduleItem->order_id) {
                    $staffSchedule->order_id = $scheduleItem->order_id;
                }
                $staffSchedule->elapsed_time = $new_interval;
                $flag = $staffSchedule->save();

                echo "End parse schedule with id {$scheduleItem->id} -- {$flag}\n";
            }
        }
    }

    public function safeDown()
    {

    }

}
