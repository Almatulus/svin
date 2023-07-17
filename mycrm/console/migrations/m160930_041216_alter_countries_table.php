<?php

use core\models\Country;
use yii\db\Migration;

class m160930_041216_alter_countries_table extends Migration
{
    public function up()
    {
        $this->addColumn('crm_countries', 'active', $this->boolean()->defaultValue(true));

        $data = [
            'Азербайджан',
            'Армения',
            'Белоруссия',
            'Казахстан',
            'Киргизия',
            'Молдавия',
            'Россия',
            'Таджикистан',
            'Туркмения',
            'Узбекистан',
            'Украина',
            'Грузия',
        ];

        $this->update('crm_countries', ['active' => false]);

        foreach ($data as $key => $name) {
            $country = Country::find()->where(['name' => $name])->one();
            if ($country) {
                $country->active = true;
                if (!$country->save()) {
                    echo $name . " saving fail\n";
                }
            }
        }
    }

    public function down()
    {
        $this->dropColumn('crm_countries', 'active');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
