<?php

namespace frontend\modules\finance\controllers;

use core\forms\finance\CashForm;
use core\forms\finance\CashTransferForm;
use core\forms\finance\CashUpdateForm;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\services\CompanyCashService;
use Exception;
use frontend\modules\finance\components\FinanceController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * CashController implements the CRUD actions for CompanyCash model.
 */
class CashController extends FinanceController
{
    private $companyCashService;

    /**
     * CashController constructor.
     *
     * @param string             $id
     * @param \yii\base\Module   $module
     * @param CompanyCashService $companyCashService
     * @param array              $config
     */
    public function __construct(
        $id,
        $module,
        CompanyCashService $companyCashService,
        $config = []
    ) {
        $this->companyCashService = $companyCashService;
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
                        'actions' => ['index', 'view'],
                        'allow'   => true,
                        'roles'   => ['cashView'],
                    ],
                    [
                        'actions' => ['create', 'edit', 'delete', 'transfer'],
                        'allow'   => true,
                        'roles'   => ['cashCreate'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ]
            ]
        ];
    }

    /**
     * Lists all CompanyCash models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CompanyCash::find()->division()->active(),
        ]);

        $incomeProvider = new ActiveDataProvider([
            'query' => CompanyCashflow::find()
                ->company(\Yii::$app->user->identity->company_id)
                ->active()
                ->income()
                ->permittedDivisions()
                ->week(),
            'sort'  => [
                'defaultOrder' => [
                    'date' => SORT_DESC,
                    'id'   => SORT_DESC
                ]
            ]
        ]);

        $expenseProvider = new ActiveDataProvider([
            'query' => CompanyCashflow::find()
                ->company(\Yii::$app->user->identity->company_id)
                ->active()
                ->expense()
                ->permittedDivisions()
                ->week(),
            'sort'  => [
                'defaultOrder' => [
                    'date' => SORT_DESC,
                    'id'   => SORT_DESC
                ]
            ]
        ]);

        return $this->render('index', [
            'dataProvider'    => $dataProvider,
            'incomeProvider'  => $incomeProvider,
            'expenseProvider' => $expenseProvider,
        ]);
    }

    /**
     * Displays a single CompanyCash model.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CompanyCashflow::find()
                ->company(\Yii::$app->user->identity->company_id)
                ->active()
                ->permittedDivisions()
                ->cash($id),
            'sort'  => ['defaultOrder' => ['date' => SORT_DESC]]
        ]);

        return $this->render('view', [
            'model'           => $this->findModel($id),
            'dataProvider'    => $dataProvider,
        ]);
    }

    /**
     * Creates a new CompanyCash model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $form = new CashForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $model = $this->companyCashService->add(
                    $form->comments,
                    $form->init_money,
                    $form->is_deletable,
                    $form->name,
                    $form->type,
                    $form->division_id
                );
                Yii::$app->session->setFlash('success',
                    Yii::t('app', 'Successful saving'));

                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * @param $id
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEdit($id)
    {
        $model = $this->findModel($id);
        $form  = new CashUpdateForm($model);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->companyCashService->edit(
                    $id,
                    $form->comments,
                    $form->name,
                    $form->init_money
                );
                Yii::$app->session->setFlash('success',
                    Yii::t('app', 'Successful saving'));
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            $this->companyCashService->delete($id);

            return json_encode([
                'message' => Yii::t('app', 'Successful deleted')
            ]);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     */
    public function actionTransfer($id)
    {
        $model = $this->findModel($id);

        $form = new CashTransferForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->companyCashService->transfer($id, $form->cash_id, $form->amount, Yii::$app->user->id);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        } else {
            Yii::$app->session->setFlash('error', current($form->firstErrors));
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the CompanyCash model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return CompanyCash the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CompanyCash::find()->division()->active()->andWhere(['id' => $id])
                                 ->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param int $type
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function actionExport($type = CompanyCostItem::TYPE_ALL)
    {

        $type = intval($type);

        $query = CompanyCashflow::find()
            ->company(\Yii::$app->user->identity->company_id)
            ->active();
        if ($type === CompanyCostItem::TYPE_INCOME) {
            $query = $query->income();
        }
        if ($type === CompanyCostItem::TYPE_EXPENSE) {
            $query = $query->expense();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $cashflows = $dataProvider->models;

        ob_start();

        $ea = new \PHPExcel(); // ea is short for Excel Application
        $ea->getProperties()
           ->setCreator('Reone')
           ->setTitle('PHPExcel')
           ->setLastModifiedBy('Reone')
           ->setDescription('')
           ->setSubject('')
           ->setKeywords('excel php')
           ->setCategory('');
        $ews = $ea->getSheet(0);
        $ews->setTitle('Движения средств');

        $cashflowLabel = new CompanyCashflow();
        $data          = [
            [
                $cashflowLabel->getAttributeLabel('id'),
                $cashflowLabel->getAttributeLabel('date'),
                $cashflowLabel->getAttributeLabel('cost_item_id'),
                $cashflowLabel->getAttributeLabel('cash_id'),
                $cashflowLabel->getAttributeLabel('contractor_id'),
                $cashflowLabel->getAttributeLabel('value'),
                $cashflowLabel->getAttributeLabel('comment'),
                $cashflowLabel->getAttributeLabel('user_id'),
            ]
        ];

        foreach ($cashflows as $cashflow) {
            /* @var $cashflow CompanyCashflow */
            $data[] = [
                $cashflow->id,
                $cashflow->date,
                $cashflow->costItem->getFullName(),
                $cashflow->cash->name,
                $cashflow->contractor ? $cashflow->contractor->name : '',
                $cashflow->value,
                $cashflow->comment,
                $cashflow->user->username,
            ];
        }

        $ews->fromArray($data, ' ', 'A1');
        $ews->getColumnDimension('A')->setWidth(4); // ID
        $ews->getColumnDimension('B')->setWidth(20); // Date
        $ews->getColumnDimension('C')->setWidth(22); // CostItem
        $ews->getColumnDimension('D')->setWidth(20); // Cash
        $ews->getColumnDimension('E')->setWidth(13); // Contractor
        $ews->getColumnDimension('F')->setWidth(14); // Value
        $ews->getColumnDimension('G')->setWidth(25); // Comment
        $ews->getColumnDimension('H')->setWidth(20); // User

        header('Content-Type: application/vnd.ms-excel');
        $filename = "Движение_средств_" . date("d-m-Y-His") . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($ea, 'Excel5');
        $objWriter->save('php://output');

        ob_end_flush();
    }
}
