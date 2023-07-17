<?php

use yii\db\Migration;

class m170606_031950_add_company_attributes extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'address', $this->string());
        $this->addColumn('{{%companies}}', 'iik', $this->string());
        $this->addColumn('{{%companies}}', 'bank', $this->string());
        $this->addColumn('{{%companies}}', 'bin', $this->string());
        $this->addColumn('{{%companies}}', 'bik', $this->string());
        $this->addColumn('{{%companies}}', 'phone', $this->string());
        $this->addColumn('{{%companies}}', 'license_issued', $this->date());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'address');
        $this->dropColumn('{{%companies}}', 'iik');
        $this->dropColumn('{{%companies}}', 'bank');
        $this->dropColumn('{{%companies}}', 'bin');
        $this->dropColumn('{{%companies}}', 'bik');
        $this->dropColumn('{{%companies}}', 'phone');
        $this->dropColumn('{{%companies}}', 'license_issued');
    }
}
