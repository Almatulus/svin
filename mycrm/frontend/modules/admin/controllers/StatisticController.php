<?php

namespace frontend\modules\admin\controllers;

use core\services\AdminStatisticsService;
use frontend\modules\admin\forms\StatisticForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;


class StatisticController extends Controller
{
    private $service;

    public function __construct(
        $id,
        $module,
        AdminStatisticsService $statisticsService,
        $config = []
    )
    {
        $this->service = $statisticsService;
        parent::__construct($id, $module, $config = []);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index'
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new StatisticForm();
        $model->load(Yii::$app->request->get());
        $model->validate();
        $statisticsData = $this->service->getMainStatisticsData($model->from, $model->to);
        $dataProvider = $model->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'model'          => $model,
            'statisticsData' => $statisticsData,
            'dataProvider'   => $dataProvider
        ]);
    }

}
