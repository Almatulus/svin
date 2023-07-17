<?php

use core\helpers\GenderHelper;
use yii\db\Migration;

class m171020_110239_setup_staff_gender extends Migration
{
    const STAFF_GENDER_NONE = 0;
    const STAFF_GENDER_MALE = 1;
    const STAFF_GENDER_FEMALE = 2;

    public function safeUp()
    {
        $this->update('{{%staffs}}', ['gender' => GenderHelper::GENDER_FEMALE], ['gender' => self::STAFF_GENDER_FEMALE]);
        $this->update('{{%staffs}}', ['gender' => GenderHelper::GENDER_MALE], ['gender' => self::STAFF_GENDER_MALE]);
        $this->update('{{%staffs}}', ['gender' => GenderHelper::GENDER_UNDEFINED], ['gender' => self::STAFF_GENDER_NONE]);
    }

    public function safeDown()
    {
        $this->update('{{%staffs}}', ['gender' => self::STAFF_GENDER_NONE], ['gender' => GenderHelper::GENDER_UNDEFINED]);
        $this->update('{{%staffs}}', ['gender' => self::STAFF_GENDER_MALE], ['gender' => GenderHelper::GENDER_MALE]);
        $this->update('{{%staffs}}', ['gender' => self::STAFF_GENDER_FEMALE], ['gender' => GenderHelper::GENDER_FEMALE]);
    }
}
