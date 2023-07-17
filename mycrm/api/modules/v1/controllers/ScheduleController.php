<?php

namespace api\modules\v1\controllers;

use core\models\Staff;
use core\models\division\DivisionService;
use api\modules\v1\components\ApiController;
use DateTime;
use yii\web\NotFoundHttpException;

class ScheduleController extends ApiController
{
    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        if (\Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            \Yii::$app->response->headers->set("Access-Control-Allow-Origin", "*");
            \Yii::$app->response->headers->set("X-Content-Type-Options", "nosniff");
            \Yii::$app->response->headers->set("Access-Control-Allow-Headers", "X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers");
            \Yii::$app->end();
        }

        $formats = [
            'full' => 'Y-m-d H:i:s',
            'time' => 'H:i:s',
            'date' => 'Y-m-d',
        ];
        $staff_id = \Yii::$app->request->getQueryParam('staff', null);
        $division_service_id = \Yii::$app->request->getQueryParam('service', null);
        $start_time = \Yii::$app->request->getQueryParam('from', null);
        $finish_time = \Yii::$app->request->getQueryParam('until', null);
        $format = \Yii::$app->request->getQueryParam('format', 'full');

        if ($division_service_id == null) {
            return ['error' => 400, 'message' => 'Service error'];
        }
        if ($start_time == null) {
            return ['error' => 400, 'message' => 'Start time error'];
        }
        if ($finish_time == null) {
            return ['error' => 400, 'message' => 'Finish time error'];
        }
        if (!isset($formats[$format])) {
            return ['error' => 400, 'message' => 'Format error'];
        }

        $start_date = new \DateTime($start_time);
        if ($start_date < (new \DateTime())) {
            $start_date = new \DateTime();
            $start_date->setTime(intval(date("G")) + 1, 0);
        }
        $finish_date = new \DateTime($finish_time);
        $division_service = DivisionService::findOne($division_service_id);
        if ($division_service) {
            $staff = Staff::findOne(['id' => $staff_id]);

            if ($format == 'date') {
                $listOfTime = $staff->getAvailableDates(
                    $division_service->average_time,
                    $start_date,
                    $finish_date
                );
            } else {
                $listOfTime = $staff->getAvailableSchedule($division_service, $start_date, $finish_date);
            }

            $filter = [];
            foreach ($listOfTime as $time) {
                $filter[] = (new DateTime($time))->format($formats[$format]);
            }
            $filter = array_filter($filter, function ($v, $k) use ($filter) {
                return isset($filter[$k + 1]) ? $filter[$k + 1] != $v : false;
            }, ARRAY_FILTER_USE_BOTH);

            $result = [];
            foreach ($filter as $key => $item) {
                $result[] = $item;
            }
            return $result;
        } else {
            return ['error' => 404, 'message' => 'Division with this service not found'];
        }
    }
}