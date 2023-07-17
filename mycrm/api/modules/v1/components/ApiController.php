<?php

namespace api\modules\v1\components;

use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

class ApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($event)
    {
        \Yii::$app->response->headers->add(
            "Access-Control-Allow-Origin", "*");
        \Yii::$app->response->headers->add(
            "X-Content-Type-Options", "nosniff");
        \Yii::$app->response->headers->add(
            "Access-Control-Allow-Headers",
            "X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers"
        );
        if (\Yii::$app->getRequest()->getMethod() == 'OPTIONS') {
            \Yii::$app->end();
        }
        return parent::beforeAction($event);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON
                ]
            ],
        ];
    }
}
