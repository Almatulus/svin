<?php

use yii\db\Migration;

class m170805_174049_alter_staff_division_service extends Migration
{
    public function safeUp()
    {
        $sql = <<<SQL
DELETE FROM 
  crm_staff_division_services
WHERE 
  id IN (
    SELECT id
      FROM (
        SELECT 
          id, 
          ROW_NUMBER() OVER (partition BY division_service_id, staff_id ORDER BY id) AS rnum
        FROM
          crm_staff_division_services
      ) t
    WHERE t.rnum > 1
  );
SQL;

        Yii::$app->getDb()->createCommand($sql)->execute();

        $this->createIndex(
            'uq_staff_division_services_division_service_staff',
            '{{%staff_division_services}}',
            ['division_service_id', 'staff_id'],
            true
        );
    }

    public function safeDown()
    {
        $this->dropIndex(
            'uq_staff_division_services_division_service_staff',
            '{{%staff_division_services}}'
        );
    }
}
