<?php

namespace frontend\modules\admin\controllers;

use frontend\modules\admin\search\UserLogSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * UserLogController implements the CRUD actions for UserLog model.
 */
class UserLogController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow'   => true,
                        'roles'   => ['administrator'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all UserLog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}
