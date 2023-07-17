<?php

namespace frontend\controllers;

use frontend\search\StaffReviewSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * StaffReviewController implements the CRUD actions for StaffReview model.
 */
class StaffReviewController extends Controller
{
    /**
     * @inheritdoc
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
                        'allow'   => true,
                        'roles'   => ['staffReviewAdmin'],
                    ],
                    [
                        'actions' => ['*'],
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all StaffReview models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StaffReviewSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
