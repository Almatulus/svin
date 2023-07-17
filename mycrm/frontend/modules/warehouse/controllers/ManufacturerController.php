<?php

namespace frontend\modules\warehouse\controllers;

use core\forms\warehouse\manufacturer\ManufacturerCreateForm;
use core\forms\warehouse\manufacturer\ManufacturerUpdateForm;
use core\models\warehouse\Manufacturer;
use core\models\warehouse\ManufacturerSearch;
use core\services\warehouse\ManufacturerService;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ManufacturerController implements the CRUD actions for Manufacturer model.
 */
class ManufacturerController extends Controller
{
    private $service;

    public function __construct($id, $module, ManufacturerService $manufacturerService, $config = [])
    {
        $this->service = $manufacturerService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['new', 'edit', 'delete', 'index', 'create', 'update'],
                'rules' => [
                    [
                        'actions' => ['new', 'edit', 'delete', 'index', 'create', 'update'],
                        'allow'   => true,
                        'roles'   => ['warehouseAdmin'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->getView()->params['bodyID'] = 'stock';
            $this->getView()->params['sideNavView'] = 'list';
            $this->getView()->params['sideNavID'] = 'warehouse';
            $this->getView()->params['sideNavOptions'] = self::getPanelItems();

            return true;
        }

        return false;
    }

    /**
     * Returns sub menu list
     */
    private static function getPanelItems()
    {
        $menu = [
            [
                'label'  => Yii::t('app', 'Deliveries history'),
                'icon'   => 'icon sprite-stock_delivery_history',
                'url'    => ['delivery/index'],
                'active' => strpos(Yii::$app->request->url, 'warehouse/delivery') !== false &&
                    strpos(Yii::$app->request->url, 'low-stock') === false
            ],
            [
                'label'  => Yii::t('app', 'Minimum stock level'),
                'icon'   => 'icon sprite-stock_low_availability',
                'url'    => ['delivery/low-stock'],
                'active' => strpos(Yii::$app->request->url, 'warehouse/delivery/low-stock') !== false
            ],
            [
                'label'  => Yii::t('app', 'Manufacturers'),
                'icon'   => 'icon sprite-stock_producer',
                'url'    => ['manufacturer/index'],
                'active' => strpos(Yii::$app->request->url, 'warehouse/manufacturer') !== false
            ]
        ];

        return $menu;
    }

    /**
     * Lists all Manufacturer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ManufacturerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $form = new ManufacturerCreateForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $this->service->create($form->name, Yii::$app->user->identity->company_id);
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $form,
            ]);
        }
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new ManufacturerUpdateForm($model);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $this->service->edit($id, $form->name);
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $form,
            ]);
        }
    }

    public function actionNew()
    {
        $form = new ManufacturerCreateForm();
        if (Yii::$app->request->isPost) {
            if ($form->load(Yii::$app->request->post()) && $form->validate()) {
                $model = $this->service->create($form->name, Yii::$app->user->identity->company_id);
                return Json::encode(['status' => "success", 'data' => $model->attributes]);
            }
            return Json::encode(["errors" => $form->errors]);
        }
        return $this->renderAjax('form', ['model' => $form]);
    }

    public function actionEdit($id)
    {
        $model = $this->findModel($id);
        $form = new ManufacturerUpdateForm($model);
        if (Yii::$app->request->isPost) {
            if ($form->load(Yii::$app->request->post()) && $form->validate()) {
                $model = $this->service->edit($id, $form->name);
                return Json::encode(['status' => "success", 'data' => $model->attributes]);
            }
            return Json::encode(["errors" => $form->errors]);
        }
        return $this->renderAjax('form', ['model' => $form]);
    }

    /**
     * Deletes an existing ServiceCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            if ($model->delete()) {
                Yii::$app->response->setStatusCode('200');
                return Json::encode("success");
            } else {
                Yii::$app->response->setStatusCode('400');
                return Json::encode("error");
            }
        } catch (Exception $e) {
            Yii::$app->response->setStatusCode('500');
            return Json::encode("error");
        }
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Manufacturer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Manufacturer::find()->company()->byId($id)->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
