<?php

namespace frontend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class Oauth2Controller extends Controller
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionIndex()
    {
        if (isset($_GET['code'])) {
            $client = Yii::$app->googleApiClient->client;
            $authCode = $_GET['code'];
            $client->fetchAccessTokenWithAuthCode($authCode);
            $refresh_token = $client->getRefreshToken();

            $state = Yii::$app->request->get("state", null);
            if ($state && $refresh_token) {
                $state = json_decode($state, true);
                $staff_id = $state['staff_id'];
                $staff = \core\models\Staff::findOne($staff_id);
                if (isset($staff->user)) {
                    $staff->user->google_refresh_token = $refresh_token;
                    $staff->user->update();
                    Yii::$app->googleApiClient->import($staff_id);
                    // exec("(php " . Yii::$app->basePath . "/yii google-calendar/import {$staff_id}) &");
                }
                return $this->redirect(['/staff/view', 'id' => $staff_id]);
            }
        }
        return $this->redirect(['/staff/index']);
    }
}