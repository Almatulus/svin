<?php

namespace api\modules\v2\controllers\system;

use api\modules\v2\search\division\DivisionSearch;
use api\modules\v2\OptionsTrait;
use Yii;
use yii\rest\ActiveController;
use yii\rest\Controller;

class AppController extends Controller
{
    use OptionsTrait;

    public function actionIos()
    {
        return [
            'name' => Yii::$app->name,
            'version' => (string) Yii::$app->params['app_ios_version'],
            'update_url' => (string) Yii::$app->params['app_ios_update_url'],
        ];
    }

    public function actionAndroid()
    {
        return [
            'name' => Yii::$app->name,
            'version' => (string) Yii::$app->params['app_android_version'],
            'update_url' => (string) Yii::$app->params['app_android_update_url'],
        ];
    }

    public function actionOptions()
    {
        $this->getOptionsHeaders();
    }
}
