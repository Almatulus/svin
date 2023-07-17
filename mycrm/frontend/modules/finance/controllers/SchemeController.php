<?php

namespace frontend\modules\finance\controllers;

use common\components\Model;
use core\forms\finance\PayrollForm;
use core\forms\finance\PayrollUpdateForm;
use core\models\finance\Payroll;
use core\models\finance\PayrollService;
use core\models\finance\PayrollStaff;
use core\services\PayrollService as SchemeService;
use frontend\modules\finance\components\FinanceController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * SchemeController implements the CRUD actions for Payroll model.
 */
class SchemeController extends FinanceController
{
    private $schemeService;

    /**
     * SchemeController constructor.
     *
     * @param string           $id
     * @param \yii\base\Module $module
     * @param SchemeService    $schemeService
     * @param array            $config
     */
    public function __construct(
        $id,
        $module,
        SchemeService $schemeService,
        $config = []
    ) {
        $this->schemeService = $schemeService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow'   => true,
                        'roles'   => ['schemeAdmin'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Payroll models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Payroll::find()
                              ->where(['company_id' => Yii::$app->user->identity->company_id])
                              ->orderBy('id'),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Payroll model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $form          = new PayrollForm();
        $modelsService = [new PayrollService()];
        $modelsStaff   = [new PayrollStaff()];
        if ($form->load(Yii::$app->request->post())) {

            $modelsService = Model::createMultiple(PayrollService::className());
            Model::loadMultiple($modelsService, Yii::$app->request->post());

            $modelsStaff = Model::createMultiple(PayrollStaff::className());
            Model::loadMultiple($modelsStaff, Yii::$app->request->post());

            // validate all models
            $valid = $form->validate();
            $valid = Model::validateMultiple($modelsService) && $valid;
            $valid = Model::validateMultiple($modelsStaff) && $valid;

            if ($valid) {
                try {
                    $model = $this->schemeService->add(
                        $form->dto,
                        $modelsService,
                        $modelsStaff
                    );
                    Yii::$app->session->setFlash('success',
                        Yii::t('app', 'Successful saving'));

                    return $this->redirect(['update', 'id' => $model->id]);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model'    => $form,
            'services' => (empty($modelsService)) ? [new PayrollService()]
                : $modelsService,
            'staffs'   => (empty($modelsStaff)) ? [new PayrollStaff()]
                : $modelsStaff,
        ]);
    }


    /**
     * Updates an existing Payroll model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model         = $this->findModel($id);
        $form          = new PayrollUpdateForm($model);
        $modelsService = $form->payroll->payrollServices;
        $modelsStaff   = $form->payroll->payrollStaffs;

        if ($form->load(Yii::$app->request->post())) {
            $oldIDs        = ArrayHelper::getColumn($modelsService, 'id');
            $modelsService = Model::createMultiple(PayrollService::className(),
                $modelsService);
            Model::loadMultiple($modelsService, Yii::$app->request->post());
            $deletedServiceIDs = array_diff($oldIDs,
                ArrayHelper::getColumn($modelsService, 'id'));

            $oldIDs      = ArrayHelper::getColumn($modelsStaff, 'id');
            $modelsStaff = Model::createMultiple(PayrollStaff::className(),
                $modelsStaff);
            Model::loadMultiple($modelsStaff, Yii::$app->request->post());
            $deletedStaffIDs = array_diff($oldIDs,
                ArrayHelper::getColumn($modelsStaff, 'id'));

            // validate all models
            $valid = $form->validate();
            $valid = Model::validateMultiple($modelsService) && $valid;
            $valid = Model::validateMultiple($modelsStaff) && $valid;

            if ($valid) {
                try {
                    $model = $this->schemeService->edit(
                        $id,
                        $form->dto,
                        $modelsService,
                        $modelsStaff,
                        $deletedServiceIDs,
                        $deletedStaffIDs
                    );
                    Yii::$app->session->setFlash('success',
                        Yii::t('app', 'Successful saving'));

                    return $this->redirect(['update', 'id' => $model->id]);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('update', [
            'model'    => $form,
            'services' => (empty($modelsService)) ? [new PayrollService]
                : $modelsService,
            'staffs'   => (empty($modelsStaff)) ? [new PayrollStaff()]
                : $modelsStaff,
        ]);
    }

    /**
     * Deletes an existing Payroll model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            $this->schemeService->delete($model->id);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Payroll model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Payroll the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Payroll::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
