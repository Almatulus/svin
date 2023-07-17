<?php

namespace frontend\modules\finance\controllers;

use common\components\excel\ExcelFileConfig;
use common\components\excel\ExcelRow;
use core\forms\finance\DailyReportForm;
use core\forms\finance\ReportForm;
use core\models\order\Order;
use core\services\finance\ReportService;
use frontend\modules\finance\components\FinanceController;
use frontend\modules\finance\search\BalanceReportSearch;
use frontend\modules\finance\search\OrderReferrerSearch;
use Yii;
use yii\filters\AccessControl;

class ReportController extends FinanceController
{
    private $service;

    public function __construct(string $id, $module, ReportService $reportService, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $reportService;
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
                        'actions' => ['balance', 'balance-export'],
                        'allow'   => true,
                        'roles'   => ['reportBalanceView'],
                    ],
                    [
                        'actions' => ['daily', 'daily-export'],
                        'allow'   => true,
                        'roles'   => ['reportStaffView'],
                    ],
                    [
                        'actions' => ['period', 'period-export'],
                        'allow'   => true,
                        'roles'   => ['reportPeriodView'],
                    ],
                    [
                        'actions' => ['referrer', 'export'],
                        'allow'   => true,
                        'roles'   => ['reportReferrerView'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Shows the financial report for certain period
     *
     * @return mixed
     */
    public function actionPeriod()
    {
        $model = new ReportForm();
        $get = Yii::$app->request->get();
        $model->load($get);

        $incomeCostItems = $model->getIncomeCostItems();
        $expenseCostItems = $model->getExpenseCostItems();
        $period = $model->getPeriod();
        $cashFlows = $model->getCashFlowsData();

        return $this->render('period',
            compact(
                'model',
                'incomeCostItems',
                'expenseCostItems',
                'period',
                'cashFlows'
            )
        );
    }

    /**
     * Exports excel file
     */
    public function actionPeriodExport()
    {
        $model = new ReportForm();
        $get   = Yii::$app->request->get();
        $model->load($get);
        $model->export();
    }

    /**
     * @return string
     */
    public function actionDaily()
    {
        $model            = new DailyReportForm();
        $model->load(Yii::$app->request->queryParams);
        $models           = $model->search();

        return $this->render('daily', [
            'model'            => $model,
            'models'           => $models,
            'paymentsList'     => $model->getPaymentList()
        ]);
    }

    /**
     * Exports excel file for daily report
     */
    public function actionDailyExport()
    {
        $model        = new DailyReportForm();
        $model->load(Yii::$app->request->queryParams);
        $paymentsList = $model->getPaymentList();

        return $model->exportExcel($paymentsList);
    }

    /**
     * @return string
     */
    public function actionBalance()
    {
        $model        = new BalanceReportSearch();
        $dataProvider = $model->search(Yii::$app->request->queryParams);

        return $this->render('balance', [
            'model'        => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionBalanceExport()
    {
        $model        = new BalanceReportSearch();
        $dataProvider = $model->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $this->service->exportBalance($dataProvider->getModels());
    }

    /**
     * @return string
     */
    public function actionReferrer()
    {
        $model        = new OrderReferrerSearch();
        $dataProvider = $model->search(Yii::$app->request->queryParams);

        if ($model->action == 'export') {
            $dataProvider->pagination = false;
            return $this->generateReport($dataProvider->getModels());
        }

        return $this->render('order-referrer', [
            'searchModel'  => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionExport()
    {
        $model       = new OrderReferrerSearch();
        $model->to   = null;
        $model->from = null;
        $dataProvider = $model->search(array());
        $dataProvider->pagination = false;
        return $this->generateReport($dataProvider->getModels());
    }

    /**
     * Generate report excel file
     *
     * @param $models
     *
     * @return mixed
     */
    private function generateReport($models)
    {
        $titles = [
            Yii::t('app', 'Datetime'),
            Yii::t('app', 'Customer'),
            Yii::t('app', 'Staff'),
            Yii::t('app', 'Services'),
            Yii::t('app', 'Referrer'),
            Yii::t('app', 'Customer Source'),
            Yii::t('app', 'Paid'),
        ];

        $rows = array_merge(
            [new ExcelRow($titles, true)],
            array_map(
                function (Order $model) {
                    $source = $model->companyCustomer->source;
                    return new ExcelRow([
                        Yii::$app->formatter->asDatetime($model->datetime),
                        $model->companyCustomer->customer->getFullName(),
                        $model->staff->getFullName(),
                        $model->getServicesTitle(', '),
                        $model->referrer->name,
                        $source !== null ? $source->name : '',
                        $model->getIncomeCash()
                    ]);
                },
                $models
            )
        );

        $filename = Yii::t('app', 'Referrer report') . '_' . Yii::$app->formatter->asDatetime(new \DateTime());
        return Yii::$app->excel->generateReport(
            new ExcelFileConfig(
                $filename,
                Yii::$app->name,
                'Title'
            ),
            $rows
        );
    }
}
