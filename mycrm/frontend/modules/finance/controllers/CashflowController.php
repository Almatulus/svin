<?php

namespace frontend\modules\finance\controllers;

use core\forms\finance\CashflowForm;
use core\forms\finance\CashflowUpdateForm;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\order\Order;
use core\services\CompanyCashflowService;
use frontend\modules\finance\components\FinanceController;
use frontend\modules\finance\search\CashflowSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * CashflowController implements the CRUD actions for CompanyCashflow model.
 */
class CashflowController extends FinanceController
{
    private $cashflowService;

    /**
     * CashflowController constructor.
     *
     * @param string $id
     * @param \yii\base\Module $module
     * @param CompanyCashflowService $cashflowService
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        CompanyCashflowService $cashflowService,
        $config = []
    ) {
        $this->cashflowService = $cashflowService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete-debt-payment' => ['post'],
                    'delete'              => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'create-income',
                            'create-expense',
                            'update',
                            'delete',
                            'delete-debt-payment',
                        ],
                        'allow'   => true,
                        'roles'   => ['companyCashflowAdmin'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all CompanyCashflow models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CashflowSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CompanyCashflow model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CompanyCashflow model.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionCreateIncome()
    {
        return $this->create(CompanyCostItem::TYPE_INCOME);
    }

    /**
     * Creates a new CompanyCashflow model.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionCreateExpense()
    {
        return $this->create(CompanyCostItem::TYPE_EXPENSE);
    }

    /**
     * Creates a new CompanyCashflow model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     *
     * @param $type
     *
     * @return mixed
     * @throws \Exception
     */
    private function create($type)
    {
        $form = new CashflowForm(Yii::$app->user->id);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->cashflowService->add($form);

                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Successful saving')
                );

                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
            'type'  => $type
        ]);
    }

    /**
     * Updates CompanyCashflow model.
     *
     * @param $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $this->findModel($id);

        $form = new CashflowUpdateForm($id, Yii::$app->user->id);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->cashflowService->edit($id, $form);

                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Successful saving')
                );

                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $form
        ]);
    }

    /**
     * Deletes CompanyCashflow model.
     *
     * @param int $id CompanyCashflow model id.
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            try {
                $this->cashflowService->delete($id);
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t('app', 'Successful deleted')
                );
                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('delete', ['model' => $model]);
    }

    /**
     * Deletes CompanyCashflow model.
     *
     * @param int $id CompanyCashflow model id.
     *
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDeleteDebtPayment($id)
    {
        $model = $this->findModel($id);

        if (!$model->isDeletableDebtPayment()) {
            throw new ForbiddenHttpException();
        }

        try {
            $this->cashflowService->deleteDebtPayment($id);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful deleted'));
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->goBack();
    }

    /**
     * Finds the CompanyCashflow model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return CompanyCashflow the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = CompanyCashflow::find()
            ->company(\Yii::$app->user->identity->company_id)
            ->andWhere(['{{%company_cashflows}}.id' => $id])
            ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
