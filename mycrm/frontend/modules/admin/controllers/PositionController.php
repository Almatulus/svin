<?php

namespace frontend\modules\admin\controllers;

use core\forms\PositionCreateForm;
use core\forms\PositionUpdateForm;
use core\models\Position;
use core\repositories\exceptions\NotFoundException;
use core\services\document\FormService;
use core\services\PositionService;
use frontend\modules\admin\search\PositionSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * PositionController implements the CRUD actions for CompanyPosition model.
 */
class PositionController extends Controller
{
    private $positionService;
    private $documentFormService;

    public function __construct(
        $id,
        $module,
        PositionService $positionService,
        FormService $documentFormService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->positionService = $positionService;
        $this->documentFormService = $documentFormService;
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
        $searchModel = new PositionSearch();
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
        $form = new PositionCreateForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $model = $this->positionService->add(
                $form->name,
                $form->description,
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

        $form = new PositionUpdateForm($model);

        if ($form->load(Yii::$app->request->post())) {

            $model = $this->positionService->edit(
                $model->id,
                $form->name,
                $form->description,
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
        $this->positionService->delete($id);

        return $this->redirect(['index']);
    }

    /**
     * Finds the CompanyPosition model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Position the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        try {
            return $this->positionService->find($id);
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

        $form = new PositionCreateForm();
        $form->load(Yii::$app->request->post(), '');

        if ( ! $form->validate()) {
            return [
                'success' => false,
                'message' => 'error',
            ];
        }

        $model = $this->positionService->add(
            $form->name,
            $form->description,
            $form->documentForms
        );

        return [
            'success' => true,
            'id'      => $model->id,
            'name'    => $model->name,
        ];
    }
}
