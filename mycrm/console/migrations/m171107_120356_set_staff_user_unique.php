<?php

use core\models\Staff;
use yii\db\Migration;

/**
 * Class m171107_120356_set_staff_user_unique
 */
class m171107_120356_set_staff_user_unique extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $sql
              = <<<SQL
select user_id, count(*) from public.crm_staffs where user_id IS NOT NULL group by user_id HAVING count(*) > 1
SQL;
        $rows = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($rows as $row) {
            $staffs = Staff::find()
                ->where(['user_id' => $row['user_id']])
                ->orderBy(['id' => SORT_DESC]);
            foreach ($staffs->each() as $staff) {
                /* @var Staff $staff */
                $staff->user_id = $row['user_id'];
                $row['user_id'] = null;
                if ($staff->update() === false) {
                    $errors = $staff->getErrors();
                    throw new DomainException(reset($errors)[0]);
                }
            }
        }

        $this->createIndex('uq_staffs_user_id', '{{%staffs}}', 'user_id', true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('uq_staffs_user_id', '{{%staffs}}');
    }
}
