<?php

namespace frontend\modules\finance\controllers;

use core\forms\finance\SalaryCheckoutForm;
use core\forms\finance\SalaryForm;
use core\models\division\Division;
use core\models\Staff;
use core\models\StaffPayment;
use core\services\SalaryService;
use frontend\modules\finance\components\FinanceController;
use frontend\search\StaffPaymentSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class SalaryController extends FinanceController
{
    /** @var SalaryService */
    private $salaryService;

    /**
     * SalaryController constructor.
     *
     * @param string $id
     * @param \yii\base\Module $module
     * @param SalaryService $salaryService
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        SalaryService $salaryService,
        $config = []
    ) {
        $this->salaryService = $salaryService;
        parent::__construct($id, $module, $config = []);
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
                        'actions' => ['estimate', 'delete', 'clear'],
                        'allow'   => true,
                        'roles'   => ['salaryPay'],
                    ],
                    [
                        'actions' => ['index', 'view'],
                        'allow'   => true,
                        'roles'   => ['salaryReportView'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'clear'  => ['POST'],
                ]
            ]
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new StaffPaymentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionEstimate()
    {
        $salaryForm = new SalaryForm();
        $checkoutForm = new SalaryCheckoutForm();

        $services = [];

        if ($checkoutForm->load(Yii::$app->request->get()) &&
            $checkoutForm->load(Yii::$app->request->post()) && $checkoutForm->validate()) {
            try {
                $this->salaryService->add(
                    new \DateTime($checkoutForm->payment_from),
                    new \DateTime($checkoutForm->payment_till),
                    new \DateTime($checkoutForm->payment_date),
                    $checkoutForm->salary,
                    $checkoutForm->staff_id,
                    $checkoutForm->division_id,
                    Yii::$app->user->id,
                    $checkoutForm->getFilteredServices()
                );

                Yii::$app->session->setFlash(
                    'success',
                    'Зарплата успешно расчитана'
                );

                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        if ($salaryForm->load(Yii::$app->request->get()) && $salaryForm->validate()) {
            try {
                $services = $this->salaryService->fetchServices(
                    $salaryForm->staff_id,
                    $salaryForm->division_id,
                    new \DateTime($salaryForm->payment_from),
                    new \DateTime($salaryForm->payment_till)
                );
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        $orderProvider = new ArrayDataProvider([
            'allModels'  => $services,
            'pagination' => ['pageSize' => 0],
        ]);

        return $this->render('estimate', [
            'dataProvider' => $orderProvider,
            'model'        => $salaryForm,
            'checkoutForm' => $checkoutForm,
            'staffs'       => Staff::getOwnCompanyStaffList(),
            'divisions'    => Division::getOwnCompanyDivisionsList()
        ]);
    }

    /**
     * @param int $id
     * @return string
     */
    public function actionView(int $id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($model);

        return $this->render('view', ['model' => $model]);
    }

    /**
     * @param int $id
     */
    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($model);

        try {
            $this->salaryService->delete($model->id);
            Yii::$app->session->setFlash('success', 'Выдача зарплаты успешно удалена.');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        $this->redirect(['index']);
    }

    /**
     * @param int $id
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionClear(int $id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($model);

        try {
            $this->salaryService->clear($model->id, Yii::$app->user->identity->company_id);
            Yii::$app->session->setFlash('success', 'Выдача зарплаты успешно удалена.');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * ToDo Consider adding permission with specific rule
     * @param StaffPayment $model
     * @throws ForbiddenHttpException
     */
    protected function checkAccess(StaffPayment $model)
    {
        $permittedDivisions = Yii::$app->user->identity->getPermittedDivisions();
        $divisionIds = $model->staff->getDivisions()->select('id')->column();

        if (empty(array_intersect($divisionIds, $permittedDivisions))) {
            throw new ForbiddenHttpException();
        }
    }

    /**
     * @param int $id
     * @return StaffPayment
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (!($model = StaffPayment::findOne($id))) {
            throw new NotFoundHttpException("Model not found");
        }
        return $model;
    }
}