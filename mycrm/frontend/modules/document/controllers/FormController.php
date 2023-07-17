<?php

namespace frontend\modules\document\controllers;

use core\models\document\DocumentForm;
use core\services\document\FormService;
use frontend\modules\document\forms\ElementsForm;
use frontend\modules\document\search\DocumentFormSearch;
use Yii;
use yii\base\DynamicModel;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * DocumentFormController implements the CRUD actions for DocumentForm model.
 */
class FormController extends Controller
{
    protected $service;

    /**
     * FormController constructor.
     * @param string $id
     * @param Module $module
     * @param FormService $formService
     * @param array $config
     */
    public function __construct($id, Module $module, FormService $formService, array $config = [])
    {
        $this->service = $formService;

        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'create',
                            'update',
                            'elements',
                            'delete',
                            'duplicate',
                            'import',
                            'upload'
                        ],
                        'allow'   => true,
                        'roles'   => ['administrator'],
                    ],
                ],
            ],
            'verbs'  => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all DocumentForm models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DocumentFormSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DocumentForm model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new DocumentForm model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DocumentForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing DocumentForm model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionElements($id)
    {
        $model = $this->findModel($id);
        $form = new ElementsForm($id);

        if (Yii::$app->request->isAjax && $form->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($form);
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $this->service->createElements($form);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('elements', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing DocumentForm model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @param $id
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDuplicate($id)
    {
        $duplicateModel = $this->service->duplicate($this->findModel($id));

        return $this->redirect(['update', 'id' => $duplicateModel->id]);
    }

    /**
     * @param int $id
     * @return array|Response
     */
    public function actionImport(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        $form = new DynamicModel(['file']);
        $form->addRule(['file'], 'required');
        $form->addRule(['file'], 'file', ['extensions' => ['docx']]);
        $form->file = UploadedFile::getInstanceByName("ImportForm[excelFile]");

        if ($form->validate()) {
            $this->service->import($model->id, $form->file->tempName, $form->file->baseName);

            return ['message' => 'Файл успешно импортирован'];
        }

        Yii::$app->session->setFlash('error', current($form->firstErrors));
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * @param int $id
     * @return array|Response
     */
    public function actionUpload(int $id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        $form = new DynamicModel(['file']);
        $form->addRule(['file'], 'required');
        $form->addRule(['file'], 'file', ['extensions' => ['docx']]);
        $form->file = UploadedFile::getInstanceByName("ImportForm[excelFile]");

        if ($form->validate()) {
            $this->service->upload($model->id, $form->file);

            return ['message' => 'Файл успешно загружен'];
        }

        Yii::$app->session->setFlash('error', current($form->firstErrors));
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the DocumentForm model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DocumentForm the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DocumentForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
