<?php

use core\models\division\DivisionService;
use yii\db\Migration;

/**
 * Class m200206_080153_revert_services_changes
 */
class m200206_080153_revert_services_changes extends Migration
{
    private $division_id = 205;

    const DIFFERENT_PRICE = 2;
    const DIFFERENT_TIME = 3;
    const DIFFERENT_NAME = 5;
    const DIFFERENT_STATUS = 7;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $csv = array_map('str_getcsv',
            file(Yii::getAlias('@console') . '/migrations/services.csv'));
        $this->compare($csv);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $csv = array_map('str_getcsv',
            file(Yii::getAlias('@console')
                . '/migrations/services_revert.csv'));
        $this->compare($csv);
    }

    /**
     * @param DivisionService $service
     * @param integer         $price
     * @param integer         $time
     * @param string          $name
     * @param integer         $status
     *
     * @throws Exception
     */
    private function checkChanges(
        DivisionService $service,
        $price,
        $time,
        $name,
        $status
    ) {
        $error_code = 1;
        if ($service->price !== $price) {
            $error_code *= self::DIFFERENT_PRICE;
        }

        if ($service->average_time !== $time) {
            $error_code *= self::DIFFERENT_TIME;
        }

        if ($service->service_name !== $name) {
            $error_code *= self::DIFFERENT_NAME;
        }

        if ($service->status !== $status) {
            $error_code *= self::DIFFERENT_STATUS;
        }

        if ($error_code !== 1) {
            throw new Exception('Not equal', $error_code);
        }
    }

    private function compare($csv)
    {
        $division_services = DivisionService::find()
            ->division($this->division_id)
            ->indexBy('id')
            ->all();

        $count = 0;
        foreach ($csv as $line) {
            /* @var $service DivisionService */
            $service = $division_services[intval($line[0])];
            try {
                $this->checkChanges(
                    $service,
                    intval($line[1]),
                    intval($line[2]),
                    $line[3],
                    intval($line[4])
                );
            } catch (Exception $exception) {
                echo ++$count . ": " . $service->id . " === " . $exception->getCode() . "\n";
                $service->price = intval($line[1]);
                $service->average_time = intval($line[2]);
                $service->service_name = $line[3];
                $service->status = intval($line[4]);
                if (!$service->save()) {
                    echo reset($service->errors)[0];
                    echo "Not saved";
                }
            }
        }
    }
}
