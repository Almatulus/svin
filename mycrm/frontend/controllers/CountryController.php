<?php

namespace frontend\controllers;

use core\models\City;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\Response;

class CountryController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['list'],
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'list' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $out = [];
        $country_id = Yii::$app->request->post('depdrop_parents', [null]);
        $cities = City::find()->where(['country_id' => $country_id[0]])->all();
        foreach($cities as $city)
        {
            array_push($out, ['id' => $city->id, 'name' => $city->name]);
        }
        return ['output'=>$out, 'selected'=>''];
    }
}