<?php

namespace frontend\modules\company\controllers;

use core\forms\company\CompanyPositionCreateForm;
use core\forms\company\CompanyPositionUpdateForm;
use core\models\company\CompanyPosition;
use core\repositories\exceptions\NotFoundException;
use core\services\company\CompanyPositionService;
use core\services\document\FormService;
use frontend\modules\company\forms\CompanyPositionMultipleForm;
use frontend\modules\company\search\CompanyPositionSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CompanyPositionController implements the CRUD actions for CompanyPosition model.
 */
class PositionController extends Controller
{
    private $companyPositionService;
    private $documentFormService;
    private $company_id;

    public function __construct(
        $id,
        $module,
        CompanyPositionService $companyPositionService,
        FormService $documentFormService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->companyPositionService = $companyPositionService;
        $this->documentFormService = $documentFormService;
        $this->company_id = Yii::$app->user->identity->company_id;
    }

    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'delete-multiple' => ['post'],
                    'add'    => ['post']
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'add', 'delete', 'delete-multiple'],
                        'allow'   => true,
                        'roles'   => ['companyPositionAdmin'],
                    ],
                    [
                        'actions' => ['add'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all CompanyPosition models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanyPositionSearch();
        $searchModel->company_id = $this->company_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new CompanyPosition model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $form = new CompanyPositionCreateForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $model = $this->companyPositionService->add(
                $this->company_id,
                $form->name,
                $form->description,
                $form->categories,
                $form->documentForms
            );

            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Successful saving'));

            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $form,
            'documentFormList' => $this->documentFormService->getDocumentFormsList(),
        ]);
    }

    /**
     * Updates an existing CompanyPosition model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $form = new CompanyPositionUpdateForm($model);

        if ($form->load(Yii::$app->request->post())) {

            $model = $this->companyPositionService->edit(
                $this->company_id,
                $model->id,
                $form->name,
                $form->description,
                $form->categories,
                $form->documentForms
            );

            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Successful saving'));

            return $this->redirect(['update', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model' => $form,
            'documentFormList' => $this->documentFormService->getDocumentFormsList(),
        ]);
    }

    /**
     * Deletes an existing CompanyPosition model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->companyPositionService->delete($this->company_id, $id);

        return $this->redirect(['index']);
    }

    public function actionDeleteMultiple()
    {
        $form = new CompanyPositionMultipleForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $this->companyPositionService->deleteMultiple($this->company_id, $form->ids);
        }
    }

    /**
     * Finds the CompanyPosition model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return CompanyPosition the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        try {
            return $this->companyPositionService->find($this->company_id, $id);
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Add a new CompanyPosition model.
     * If creation is successful, respond with model attributes values,
     * otherwise with message about error
     *
     * @return mixed
     */
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new CompanyPositionCreateForm();
        $form->load(Yii::$app->request->post(), '');

        if ( ! $form->validate()) {
            return [
                'success' => false,
                'message' => 'error',
            ];
        }

        $model = $this->companyPositionService->add(
            $this->company_id,
            $form->name,
            $form->description,
            $form->categories,
            $form->documentForms
        );

        return [
            'success' => true,
            'id'      => $model->id,
            'name'    => $model->name,
        ];
    }
}
