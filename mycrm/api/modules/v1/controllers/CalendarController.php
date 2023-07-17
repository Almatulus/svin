<?php

namespace api\modules\v1\controllers;

use core\helpers\ICalendar;
use api\modules\v1\components\ApiController;
use core\models\user\User;
use yii\filters\auth\HttpBasicAuth;

class CalendarController extends ApiController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => HttpBasicAuth::className(),
                'auth' => [$this, 'auth']
            ]
        ];
    }

    /**
     * Validates credentials
     * @param $username
     * @param $password
     * @return null|static
     */
    public function auth($username, $password)
    {
        $user = User::findOne(['username' => $username]);
        return ($user && isset($user->staff) && $user->validatePassword($password)) ? $user : null;
    }

    /**
     * Generates calendar for staff
     */
    public function actionIndex()
    {
        $calendar = ICalendar::generate();

        header("Content-type: text/calendar; charset=utf-8");
        header("Content-Disposition: inline; filename=calendar.ics");

        echo $calendar;
        exit;
    }
}
