<?php

use core\models\company\Company;
use yii\db\Migration;

class m170207_132307_alter_company_tariff extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'tariff', $this->integer()->notNull()->defaultValue(0));

        $tariffs = [
            1 => 7000,
            2 => 14000,
            3 => 21000
        ];
        $companies = Company::find()->all();
        foreach ($companies as $company) {
            /* @var Company $company */
            $company->tariff = isset($tariffs[$company->tariff_id]) ? $tariffs[$company->tariff_id] : 0;
            if (!$company->save()) {
                throw new Exception('Moving tariff error');
            }
        }

        $this->dropColumn('{{%companies}}', 'tariff_id');
    }

    public function safeDown()
    {
        $this->addColumn('{{%companies}}', 'tariff_id', $this->integer()->defaultValue(null));
        $this->dropColumn('{{%companies}}', 'tariff');
    }
}
