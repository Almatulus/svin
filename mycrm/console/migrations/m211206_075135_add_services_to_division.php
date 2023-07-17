<?php

use yii\db\Migration;

/**
 * Class m211206_075135_add_services_to_division
 */
class m211206_075135_add_services_to_division extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $root_division = \core\models\division\Division::findOne(['id' => 205]);
        $root_services = $root_division->getDivisionServices()
            ->andWhere(['status' => \core\models\division\DivisionService::STATUS_ENABLED])
            ->all();

        foreach ($root_services as $service) {
            $sql = ' SELECT * FROM {{%service_division_map}} ' .
                ' WHERE division_service_id = ' . $service->id .
                ' AND division_id = 329';
            $exist = count(Yii::$app->db->createCommand($sql)->queryAll()) > 0;
            if (!$exist) {
                $this->insert(
                    '{{%service_division_map}}',
                    [
                        'division_service_id' => $service->id,
                        'division_id' => 329
                    ]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
