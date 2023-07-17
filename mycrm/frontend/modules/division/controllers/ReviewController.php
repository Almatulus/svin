<?php

namespace frontend\modules\division\controllers;

use frontend\modules\division\search\DivisionReviewSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * DivisionReviewController implements the CRUD actions for DivisionReview model.
 */
class ReviewController extends Controller
{
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
                        'roles'   => ['divisionReviewAdmin'],
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
     * Lists all DivisionReview models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DivisionReviewSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
