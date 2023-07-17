<?php

namespace frontend\modules\customer\controllers;

use core\models\customer\CustomerSource;
use core\services\customer\CustomerSourceService;
use frontend\modules\customer\components\CustomerModuleController;
use frontend\modules\customer\search\CompanyCustomerSourceSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * SourceController implements the CRUD actions for CustomerSource model.
 */
class SourceController extends CustomerModuleController
{
    /**
     * @var CustomerSourceService
     */
    private $service;

    public function __construct(
        string $id,
        $module,
        CustomerSourceService $service,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->service = $service;
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
                    'new'    => ['post'],
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'create',
                            'update',
                            'index',
                            'delete',
                            'move',
                        ],
                        'allow'   => true,
                        'roles'   => ['companySourceAdmin'],
                    ],
                    [
                        'actions' => [
                            'new'
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionNew()
    {
        $model = new CustomerSource();
        $model->company_id = Yii::$app->user->identity->company_id;
        $model->type       = CustomerSource::TYPE_DYNAMIC;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return Json::encode([
                'status' => "success",
                'data'   => $model->attributes,
            ]);
        }

        return Json::encode(["errors" => $model->errors]);
    }

    /**
     * @return string
     */
    public function actionCreate()
    {
        $model = new CustomerSource();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->service->create($model->name, Yii::$app->user->identity->company_id);
            return $this->redirect('index');
        }

        return $this->render('create', [
            'model' => $model
        ]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->service->update($id, $model->name);
            return $this->redirect('index');
        }

        return $this->render('update', [
            'model' => $model
        ]);
    }

    /**
     * Lists all CustomerSource models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new CompanyCustomerSourceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing CustomerSource model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->type === CustomerSource::TYPE_DEFAULT) {
            Yii::$app->session->setFlash('error',
                Yii::t('app', 'Delete Error'));
        } else
        if ($model->getCompanyCustomers()->count() > 1) {
            Yii::$app->session->setFlash('error',
                Yii::t('app', 'Delete Error'));
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success',
                Yii::t('app', 'Successful deleted'));
        }
        $redirect = Yii::$app->request->referrer ?: ['index'];

        return $this->redirect($redirect);
    }

    /**
     * @param integer $source
     * @param integer $destination
     *
     * @return array
     */
    public function actionMove($source, $destination)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'updated' => $this->service->moveCustomers(
                $source,
                $destination,
                Yii::$app->user->identity->company_id
            ),
        ];
    }

    /**
     * Finds the CustomerSource model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return CustomerSource the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CustomerSource::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
