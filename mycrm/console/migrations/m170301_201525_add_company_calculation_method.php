<?php

use core\models\company\Company;
use yii\db\Migration;

class m170301_201525_add_company_calculation_method extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'calculation_method',
            $this->integer()->notNull()->defaultValue(Company::CALCULATE_STRAIGHT));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'calculation_method');
    }
}
