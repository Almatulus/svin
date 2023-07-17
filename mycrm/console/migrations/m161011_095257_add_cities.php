<?php

use core\models\City;
use core\models\Country;
use yii\db\Migration;

class m161011_095257_add_cities extends Migration
{
    public function up()
    {
        $cities = [
            'Актау',
            'Актобе',
            'Алматы',
            'Астана',
            'Атырау',
            'Байконур',
            'Балхаш',
            'Караганда',
            'Костанай',
            'Павлодар',
            'Семей',
            'Уральск',
            'Усть-Каменогорск',
            'Шымкент',
        ];

        $country = Country::findOne(['name' => 'Казахстан']);

        if ($country) {
            foreach ($cities as $key => $cityName) {
                $city = City::findOne(['name' => $cityName]);
                if (!$city) {
                    $newCity             = new City();
                    $newCity->name       = $cityName;
                    $newCity->country_id = $country->id;
                    if (!$newCity->save()) {
                        echo $cityName . ' error save' . PHP_EOL;
                    }
                } else {
                    echo $cityName . ' already exists' . PHP_EOL;
                }
            }
        }
    }

    public function down()
    {
        echo "m161011_095257_add_cities cannot be reverted.\n";

        return false;
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
