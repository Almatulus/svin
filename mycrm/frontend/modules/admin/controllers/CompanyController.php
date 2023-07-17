<?php

namespace frontend\modules\admin\controllers;

use core\forms\company\TariffPaymentForm;
use core\helpers\company\PaymentHelper;
use core\models\company\Cashback;
use core\models\company\Company;
use core\models\company\query\CompanyQuery;
use core\models\company\TariffPayment;
use core\models\customer\CompanyCustomer;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\medCard\MedCardToothDiagnosis;
use core\models\order\Order;
use core\models\order\query\OrderQuery;
use core\services\company\CompanyService;
use core\services\dto\CompanyDetailsData;
use core\services\dto\CompanyPaymentData;
use core\services\dto\PersonData;
use frontend\modules\admin\forms\CompanyCreateForm;
use frontend\modules\admin\forms\CompanyUpdateForm;
use frontend\modules\admin\forms\PaymentLogForm;
use frontend\modules\admin\search\CompanySearch;
use frontend\modules\admin\search\DivisionSearch;
use frontend\modules\admin\search\TariffPaymentSearch;
use frontend\search\CompanyPaymentLogSearch;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CompanyController implements the CRUD actions for Company model.
 */
class CompanyController extends Controller
{
    private $companyService;

    public function __construct($id, $module, CompanyService $companyService, $config = [])
    {
        $this->companyService = $companyService;
        parent::__construct($id, $module, $config = []);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['check-deposits', 'check-cashbacks', 'check-balance'],
                        'allow'   => true,
                        'roles'   => ['administrator'],
                    ],
                    [
                        'actions' => ['index', 'active', 'payment-logs', 'payment'],
                        'allow'   => true,
                        'roles'   => ['companyView'],
                    ],
                    [
                        'actions' => ['update', 'generate-teeth-diagnoses', 'edit-tariff-payment', 'group-categories'],
                        'allow'   => true,
                        'roles'   => ['companyUpdate'],
                    ],
                    [
                        'actions' => ['create', 'add-payment', 'pay-tariff'],
                        'allow'   => true,
                        'roles'   => ['companyCreate'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Company models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Active Company models.
     * @return mixed
     */
    public function actionActive()
    {
        return $this->render('active');
    }

    /**
     * Creates a new Company model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new CompanyCreateForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $model = $this->companyService->add(
                    $form->status,
                    $form->publish,
                    $form->category_id,
                    $form->enable_web_call,
                    $form->file_manager_enabled,
                    $form->show_referrer,
                    $form->interval,
                    $form->show_new_interface,
                    new CompanyDetailsData($form->address, $form->bank, $form->bik, $form->bin, $form->iik, $form->license_issued, $form->license_number, $form->name, $form->phone),
                    new PersonData($form->head_name, $form->head_surname, $form->head_patronymic),
                    new CompanyPaymentData($form->tariff_id),
                    $form->unlimited_sms,
                    $form->notify_about_order,
                    $form->cashback_percent,
                    $form->limit_auth_time_by_schedule,
                    $form->enable_integration
                );
                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                return $this->redirect(['update', 'id' => $model->id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $form = new CompanyUpdateForm($model);
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->companyService->edit(
                    $model->id,
                    $form->status,
                    $form->publish,
                    $form->category_id,
                    $form->enable_web_call,
                    $form->file_manager_enabled,
                    $form->show_referrer,
                    $form->interval,
                    $form->show_new_interface,
                    new CompanyDetailsData($form->address, $form->bank, $form->bik, $form->bin, $form->iik, $form->license_issued, $form->license_number, $form->name, $form->phone),
                    new PersonData($form->head_name, $form->head_surname, $form->head_patronymic),
                    new CompanyPaymentData($form->tariff_id),
                    $form->unlimited_sms,
                    $form->notify_about_order,
                    $form->cashback_percent,
                    $form->limit_auth_time_by_schedule,
                    $form->enable_integration
                );
                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                return $this->redirect(['update', 'id' => $model->id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        $searchModel = new DivisionSearch();
        $searchModel->company_id = $model->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('update', [
            'model'        => $form,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param integer $id
     *
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionAddPayment($id)
    {
        $form = new PaymentLogForm();
        $form->description = Yii::t('app', 'Add balance');
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $model = $this->findModel($id);
                $this->companyService->addPayment(
                    $model->id,
                    $form->currency,
                    $form->description,
                    $form->message,
                    $form->value,
                    true
                );
                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                return $this->redirect(['update', 'id' => $model->id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('add-payment', [
            'model' => $form
        ]);
    }

    /**
     * @param integer $id Company id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPayment($id)
    {
        $company         = $this->findModel($id);

        $searchModel             = new CompanyPaymentLogSearch();
        $searchModel->company_id = $company->id;
        $dataProvider
            = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('payment', [
            'company'      => $company,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionPayTariff($id)
    {
        $company = $this->findModel($id);

        $form = new TariffPaymentForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            try {
                $this->companyService->payTariff(
                    $company->id,
                    $form->sum,
                    $form->period,
                    $form->start_date
                );

                return $this->redirect(['payment-logs', 'id' => $id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('pay-tariff', [
            'model'   => $form,
            'company' => $company
        ]);
    }


    /**
     * @param $id
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionEditTariffPayment($id)
    {
        $tariff = TariffPayment::findOne($id);

        $form = new TariffPaymentForm();
        $form->attributes = $tariff->attributes;

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $this->companyService->editTariffPayment(
                $id,
                $form->sum,
                $form->period,
                $form->start_date
            );

            return $this->redirect(['payment-logs', 'id' => $tariff->company_id]);
        }

        return $this->render('pay-tariff', [
            'model'   => $form,
            'company' => $tariff->company
        ]);
    }

    /**
     * @param $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPaymentLogs($id)
    {
        $company = $this->findModel($id);

        $searchModel = new TariffPaymentSearch(['company_id' => $id]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('logs', [
            'company'      => $company,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionGenerateTeethDiagnoses($id)
    {
        MedCardToothDiagnosis::generateDefault($this->findModel($id));

        Yii::$app->session->setFlash(
            'success',
            Yii::t('app', 'Successful saving')
        );

        return $this->redirect(['update', 'id' => $id]);
    }

    /**
     * @param $id
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionGroupCategories($id)
    {
        $company = $this->findModel($id);

        $this->companyService->groupCategories($id);

        Yii::$app->session->setFlash('success', 'Успешно сгруппированы');

        return $this->redirect('index');
    }

    /**
     * @return string
     */
    public function actionCheckDeposits()
    {
        $companies = CompanyCashflow::find()
            ->joinWith('costItem', false)
            ->andWhere([
                'cost_item_type' => [
                    CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_EXPENSE,
                    CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_INCOME
                ],
            ])
            ->all();

        $companies = ArrayHelper::index($companies, null, [
            'company_id',
            function (CompanyCashflow $model) {
                return substr(Yii::$app->formatter->asDatetime($model->updated_at, "php:Y-m-d H:i:s"), 0, -1);
            }
        ]);

        $companies = array_filter($companies, function ($companyCashflows) {
            foreach ($companyCashflows as $updated_at => $cashflows) {
                if (sizeof($cashflows) > 1) {
                    return true;
                }
            }
            return false;
        });

        return Json::encode($companies);
    }

    /**
     * @return mixed
     */
    public function actionCheckBalance()
    {
        Yii::$app->response->format = 'json';

        $week_ago = date('Y-m-d 00:00:00', time() - 7 * 24 * 60 * 60);

        $activeCompanies = Company::find()->joinWith('divisions.orders', false)
            ->select('{{%companies}}.id')
            ->andWhere([
                '>=',
                '{{%orders}}.created_time',
                $week_ago
            ])->column();

        $paymentDiffQuery = Order::find()
            ->finished()
            ->select('{{%orders}}.company_customer_id, SUM(payment_difference) as payment_difference')
            ->groupBy('{{%orders}}.company_customer_id');

        $depositPaymentQuery = Order::find()
            ->select('{{%orders}}.company_customer_id, SUM(CASE WHEN payment_id = 10 THEN amount ELSE 0 END) as deposit_payment')
            ->finished()
            ->joinWith('orderPayments', false)
            ->groupBy('{{%orders}}.company_customer_id');

        $models = CompanyCustomer::find()
            ->select([
                '{{%company_customers}}.id',
                '{{%company_customers}}.customer_id',
                '{{%customers}}.lastname',
                '{{%customers}}.name',
                '{{%companies}}.name as company',
                'balance'
            ])
            ->innerJoin(['pd' => $paymentDiffQuery], 'pd.company_customer_id = {{%company_customers}}.id')
            ->innerJoin(['dp' => $depositPaymentQuery], 'dp.company_customer_id = {{%company_customers}}.id')
            ->joinWith('customer', false)
            ->joinWith([
                'company' => function (CompanyQuery $query) {
                    return $query->enabled();
                }
            ], false)
            ->with([
                'orders' => function (OrderQuery $query) {
                    return $query->select('datetime, payment_difference, company_customer_id')->finished();
                }
            ])
            ->andWhere(['not in', 'company_id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES])
            ->andWhere(['company_id' => $activeCompanies])
            ->andWhere(['<>', 'balance', 0])
            ->andWhere('pd.payment_difference - dp.deposit_payment <> {{%company_customers}}.balance')
            ->orderBy('company_id ASC')
            ->asArray();

        return $models->all();
    }

    /**
     * @return array|Cashback[]
     */
    public function actionCheckCashbacks()
    {
        Yii::$app->response->format = 'json';

        $cashbacks = Cashback::find()->joinWith('order.orderPayments.payment')
            ->enabled()
            ->in()
            ->andWhere([
                '{{%payments}}.type' => [
                    PaymentHelper::INSURANCE,
                    PaymentHelper::DEPOSIT,
                    PaymentHelper::CASHBACK
                ]
            ])
            ->asArray()
            ->all();

        return array_filter($cashbacks, function (array $cashback) {
            $payment = 0;

            foreach ($cashback['order']['orderPayments'] as $orderPayment) {
                if ($orderPayment['payment']['id'] == PaymentHelper::CASH_ID || $orderPayment['payment']['id'] == PaymentHelper::CARD_ID) {
                    $payment += $orderPayment['amount'];
                }
            }

            return intval($payment * $cashback['percent'] / 100) != $cashback['amount'];
        });
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
