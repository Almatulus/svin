<?php

namespace api\modules\v2\controllers\cashflow;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\cashflow\CashflowSearch;
use core\models\user\User;
use Yii;
use yii\filters\auth\HttpBasicAuth;

class DefaultController extends BaseController
{
    public $modelClass = 'core\models\finance\CompanyCashflow';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::class,
            'auth'  => function ($username, $password) {
                $user = User::findByUsername($username);

                if ($user && $user->validatePassword($password)) {
                    return $user;
                }

                return null;
            }
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }


    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new CashflowSearch();
        return $searchModel->search(Yii::$app->request->queryParams);
    }
}