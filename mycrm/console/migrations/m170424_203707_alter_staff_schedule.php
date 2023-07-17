<?php

use yii\db\Migration;

class m170424_203707_alter_staff_schedule extends Migration
{
    public function safeUp()
    {
        $this->renameTable('{{%staff_schedules}}', '{{%staff_schedules_old}}');
        $this->createTable('{{%staff_schedules}}', [
            'id' => $this->primaryKey(),
            'staff_id' => $this->integer()->notNull(),
            'start_at' => $this->dateTime()->notNull(),
            'end_at' => $this->dateTime()->notNull()
        ]);
        $this->addForeignKey('fk_staff_schedules_staff', '{{%staff_schedules}}', 'staff_id', '{{%staffs}}', 'id');

        $schedules_period = (new \yii\db\Query())
            ->select('min(datetime) as start_date, max(datetime) as end_date')
            ->from('{{%staff_schedules_old}}')
            ->all();

        $start_date = new DateTime($schedules_period[0]['start_date']);
        $end_date = new DateTime($schedules_period[0]['end_date']);

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start_date, $interval, $end_date);

        $insert_data = [];
        foreach ($period as $dt) {
            $schedules = (new \yii\db\Query())
                ->select('min(datetime) as start_date, max(datetime) as end_date, staff_id')
                ->from('{{%staff_schedules_old}}')
                ->where(['>=', 'datetime', $dt->format("Y-m-d 00:00:00")])
                ->andWhere(['<=', 'datetime', $dt->format("Y-m-d 23:59:59")])
                ->groupBy('staff_id')
                ->all();

            foreach ($schedules as $index => $schedule) {
                echo $dt->format("Y-m-d \n");
                $end_datetime = (new DateTime($schedules[$index]['end_date']))
                    ->modify('+' . Yii::$app->params['scheduleInterval'] . ' minutes');
                $insert_data[] = [
                    $schedules[$index]['staff_id'],
                    $schedules[$index]['start_date'],
                    $end_datetime->format('Y-m-d H:i:s')
                ];
            }
        }

        Yii::$app->db->createCommand()
            ->batchInsert('{{%staff_schedules}}', ['staff_id', 'start_at', 'end_at'], $insert_data)->execute();
    }

    public function safeDown()
    {
        $this->dropTable('{{%staff_schedules}}');
        $this->renameTable('{{%staff_schedules_old}}', '{{%staff_schedules}}');
    }
}
