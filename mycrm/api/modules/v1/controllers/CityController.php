<?php

namespace api\modules\v1\controllers;

use core\models\City;
use api\modules\v1\components\ApiController;
use yii\web\NotFoundHttpException;

class CityController extends ApiController
{
    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        return City::find()->joinWith('country')->all();
    }

    /**
     * @return array
     */
    public function actionGeocode()
    {
        $longitude = \Yii::$app->request->getBodyParam('long', null);
        $latitude = \Yii::$app->request->getBodyParam('lat', null);

        if ($longitude !== null && $latitude !== null) {
            $model = City::find()->joinWith('country')->where(["crm_cities.name" => City::getCityName($latitude, $longitude)])->one();

            if ($model) {
                return [
                    'id' => $model->id,
                    'name' => $model->name,
                    'country_id' => $model->country_id,
                    'country_name' => $model->country->name,
                ];
            }
        }

        return [];
    }
}