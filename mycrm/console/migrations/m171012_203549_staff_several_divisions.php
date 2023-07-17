<?php

use core\models\Staff;
use core\models\order\Order;
use yii\db\Migration;

class m171012_203549_staff_several_divisions extends Migration
{
    public function safeUp()
    {
        // Staff Division Map
        $this->createTable('{{%staff_division_map}}', [
            'staff_id'    => $this->integer()->unsigned()->notNull(),
            'division_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->createIndex(
            'uq_staff_division_map_staff_division',
            '{{%staff_division_map}}',
            ['staff_id', 'division_id'],
            true
        );

        $this->addForeignKey('fk_staff_division_map_staff',
            '{{%staff_division_map}}',
            'staff_id',
            '{{%staffs}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey('fk_staff_division_map_division',
            '{{%staff_division_map}}',
            'division_id',
            '{{%divisions}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Order division_id
        $this->addColumn('{{%orders}}', 'division_id',
            $this->integer()->unsigned());

        $this->addForeignKey('fk_orders_division',
            '{{%orders}}',
            'division_id',
            '{{%divisions}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $sql = <<<SQL
UPDATE {{%orders}} AS o
SET division_id = s.division_id
FROM {{%staffs}} AS s
WHERE o.staff_id = s.id;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%orders}} ALTER COLUMN division_id SET NOT NULL;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%orders}} ALTER COLUMN staff_id SET NOT NULL;
SQL;
        $this->execute($sql);

        // Staff Schedule
        $this->addColumn(
            '{{%staff_schedules}}',
            'division_id',
            $this->integer()->unsigned()
        );

        $this->addForeignKey(
            'fk_staff_schedules_division',
            '{{%staff_schedules}}',
            'division_id',
            '{{%divisions}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $sql = <<<SQL
UPDATE {{%staff_schedules}} AS o
SET division_id = s.division_id
FROM {{%staffs}} AS s
WHERE o.staff_id = s.id;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%staff_schedules}} ALTER COLUMN division_id SET NOT NULL;
SQL;
        $this->execute($sql);

        // Staff division map insert
        $this->batchInsert(
            '{{%staff_division_map}}',
            ['staff_id', 'division_id'],
            Staff::find()
                 ->select(['id as staff_id', 'division_id'])
                 ->asArray()
                 ->all()
        );
        $this->dropColumn('{{%staffs}}', 'division_id');

        $this->dropColumn('{{%orders}}', 'division_service_id');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%staff_schedules}}', 'division_id');

        $this->addColumn('{{%orders}}', 'division_service_id', $this->integer()->unsigned());

        $this->addColumn('{{%staffs}}', 'division_id',
            $this->integer()->unsigned());
        $this->addForeignKey('fk_staffs_division',
            '{{%staffs}}',
            'division_id',
            '{{%divisions}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $sql
            = <<<SQL
UPDATE {{%staffs}} AS o
SET division_id = s.division_id
FROM {{%staff_division_map}} AS s
WHERE o.id = s.staff_id
SQL;
        $this->execute($sql);

        $this->dropColumn('{{%orders}}', 'division_id');
        $this->dropTable('{{%staff_division_map}}');
    }
}
