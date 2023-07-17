<?php

namespace frontend\controllers;

use core\forms\customer\statistic\StatisticCustomer;
use core\forms\customer\statistic\StatisticService;
use core\forms\customer\StatisticForm;
use core\forms\customer\StatisticStaffForm;
use core\forms\statistic\CostPriceForm;
use core\forms\statistic\InsuranceStatForm;
use core\models\company\Company;
use Yii;
use yii\base\Module;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class StatisticController extends Controller
{
    /** @var \core\services\StatisticService */
    private $service;

    /**
     * StatisticController constructor.
     * @param string $id
     * @param Module $module
     * @param \core\services\StatisticService $service
     * @param array $config
     */
    public function __construct($id, Module $module, \core\services\StatisticService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow'   => true,
                        'roles'   => ['statisticView'],
                    ],
                    [
                        'actions' => ['staff'],
                        'allow'   => true,
                        'roles'   => ['statisticStaffView'],
                    ],
                    [
                        'actions' => ['service', 'cost'],
                        'allow'   => true,
                        'roles'   => ['statisticServiceView'],
                    ],
                    [
                        'actions' => ['customer'],
                        'allow'   => true,
                        'roles'   => ['statisticCustomerView'],
                    ],
                    [
                        'actions' => ['insurance', 'export-insurance'],
                        'allow'   => true,
                        'roles'   => ['statisticInsuranceView'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ( ! Yii::$app->user->isGuest) {
            /* @var Company $company */
            $company = Yii::$app->user->identity->company;
            $should_goto_new_design = $company->show_new_interface && ! empty(Yii::$app->params['vue_host']);
            if ($should_goto_new_design) {
                return $this->gotoNewDesign();
            }
        }

        return parent::beforeAction($action);
    }

    // Основные
    public function actionIndex()
    {
        $model = new StatisticForm();
        $prevStat = new StatisticForm();
        $model->scenario = StatisticForm::SCENARIO_GENERAL;

        $get = Yii::$app->request->get();
        $model->load($get);

        $prevStat->load($get);
        $prevStat->to = date("Y-m-d", strtotime($model->from));
        $prevStat->from = date("Y-m-d", strtotime($model->from . " -" . ($model->difference) . " days"));

        return $this->render('index', [
            'model'    => $model,
            'prevStat' => $prevStat,
        ]);
    }

    // Сотрудники
    public function actionStaff()
    {
        $model = new StatisticStaffForm();
        $dataProvider = $model->search(Yii::$app->request->queryParams);

        return $this->render('staff', [
            'model'        => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    // Услуги
    public function actionService()
    {
        $model = new StatisticService();
        $model->load(Yii::$app->request->queryParams);

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery(),
            'sort'  => [
                'attributes'   => [
                    'service_name',
                    'revenue',
                    'orders_count',
                    'average_cost',
                ],
                'defaultOrder' => ['revenue' => SORT_DESC]
            ]
        ]);

        if(Yii::$app->request->get('action') === 'download'){
            $dataProvider->pagination = false;
            $this->service->exportServices($model, $dataProvider->models);
        }

        return $this->render('service', [
            'model'        => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    // Клиенты
    public function actionCustomer()
    {
        $model = new StatisticCustomer();
        $model->load(Yii::$app->request->get());

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery()->joinWith('customer'),
            'sort'  => [
                'attributes'   => [
                    'customer_name'  => [
                        'asc'  => ['{{%customers}}.lastname' => SORT_ASC, '{{%customers}}.name' => SORT_ASC],
                        'desc' => ['{{%customers}}.lastname' => SORT_DESC, '{{%customers}}.name' => SORT_DESC],
                    ],
                    'customer_phone' => [
                        'asc'  => ['{{%customers}}.phone' => SORT_ASC],
                        'desc' => ['{{%customers}}.phone' => SORT_DESC],
                    ],
                    'average_revenue',
                    'revenue',
                    'orders_count',
                ],
                'defaultOrder' => ['revenue' => SORT_DESC]
            ]
        ]);

        return $this->render('customer', [
            'model'        => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @return string
     */
    public function actionInsurance()
    {
        $searchModel = new InsuranceStatForm();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('insurance', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Generate excel file
     */
    public function actionExportInsurance()
    {
        $searchModel = new InsuranceStatForm();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();

        try {
            $this->service->exportInsurance($models);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', Yii::t('app', $e->getMessage()));
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    // Email, push, sms уведомления
    // public function actionNotifications() {
    //     $company = Yii::$app->user->identity->company;
    //     return $this->render('notifications', [
    //         'model' => $company->noticesInfo
    //     ]);
    // }

    /**
     * @return Response
     */
    private function gotoNewDesign()
    {
        $new_design_link = Yii::$app->params['vue_host']."/statistics";

        return $this->redirect($new_design_link);
    }

    /**
     * @return string
     */
    public function actionCost()
    {
        $searchModel = new CostPriceForm();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('cost', [
            'searchModel'   => $searchModel,
            'dataProvider'  => $dataProvider,
            'estimatedData' => $searchModel->getData($dataProvider)
        ]);
    }
}
