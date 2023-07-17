<?php

use core\models\Service;
use core\models\ServiceCategory;
use yii\db\Migration;

class m161221_151702_change_service_names extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $services = Service::find()->where([
                'crm_services.name' => Yii::t('app', 'Services'),
                'crm_service_categories.type' => ServiceCategory::TYPE_CATEGORY_DYNAMIC
            ])->joinWith(['category'])->all();

        foreach ($services as $service) {
            /* @var Service $service */
            $service->name = $service->category->name;
            if ($service->save()) {
                echo "done ";
            } else {
                echo "error ";
            }
            echo $service->name . " " . $service->category->name . "\n";
        }
    }

    public function safeDown()
    {
    }
}
