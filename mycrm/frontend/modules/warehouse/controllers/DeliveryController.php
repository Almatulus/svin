<?php

namespace frontend\modules\warehouse\controllers;

use common\components\Model;
use core\forms\warehouse\delivery\DeliveryCreateForm;
use core\forms\warehouse\delivery\DeliveryUpdateForm;
use core\models\warehouse\Delivery;
use core\models\warehouse\DeliveryProduct;
use core\models\warehouse\DeliverySearch;
use core\services\warehouse\DeliveryService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * DeliveryController implements the CRUD actions for Delivery model.
 */
class DeliveryController extends Controller
{
    private $service;

    public function __construct($id, $module, DeliveryService $deliveryService, $config = [])
    {
        $this->service = $deliveryService;
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
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'view', 'update', 'delete', 'low-stock', 'batch-delete'],
                        'allow'   => true,
                        'roles'   => ['warehouseAdmin'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete'       => ['POST'],
                    'batch-delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Delivery models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeliverySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Delivery model.
     * @param integer $id
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
     * Creates a new Delivery model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new DeliveryCreateForm();
        $products = [new DeliveryProduct()];

        if ($form->load(Yii::$app->request->post())) {

            $products = Model::createMultiple(DeliveryProduct::classname());
            Model::loadMultiple($products, Yii::$app->request->post());

            // validate all models
            $valid = $form->validate();
            $valid = Model::validateMultiple($products) && $valid;

            if ($valid) {
                try {
                    $this->service->create(
                        Yii::$app->user->identity->company_id,
                        Yii::$app->user->id,
                        $form->contractor_id,
                        $form->division_id,
                        $form->invoice_number,
                        $form->delivery_date,
                        $form->notes,
                        $products
                    );

                    return $this->redirect(['index']);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model'    => $form,
            'products' => $products
        ]);
    }

    /**
     * Updates an existing Delivery model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new DeliveryUpdateForm($model);
        $products = $model->products;

        if ($form->load(Yii::$app->request->post())) {
            $products = Model::createMultiple(DeliveryProduct::classname(), $products);
            Model::loadMultiple($products, Yii::$app->request->post());

            // validate all models
            $valid = $form->validate();
            $valid = Model::validateMultiple($products) && $valid;

            if (!$valid) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Error while saving'));
                return $this->redirect(['update', 'id' => $model->id]);
            }

            try {
                $model = $this->service->edit(
                    $id,
                    $form->contractor_id,
                    $form->division_id,
                    $form->invoice_number,
                    $form->delivery_date,
                    $form->notes,
                    $products
                );
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }

            Yii::$app->session->setFlash(
                'success',
                Yii::t('app', 'Successful saving')
            );
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model'    => $model,
            'products' => (empty($products)) ? [new DeliveryProduct] : $products
        ]);
    }

    /**
     * Deletes an existing Delivery model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            $this->service->delete($model->id);
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect('index');
    }

    /**
     * @return array
     */
    public function actionBatchDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $selected = Yii::$app->request->getBodyParam('selected');

        $data = ['deleted' => 0, 'errors' => []];
        if ($selected) {
            foreach ($selected as $delivery_id) {
                try {
                    $this->service->delete($delivery_id);
                    $data['deleted']++;
                } catch (\DomainException $e) {
                    $data['errors'][] = "Ошибка при удалении Поставки #{$delivery_id}: " . $e->getMessage();
                }
            }
        }

        return $data;
    }


    /**
     *
     */
    public function actionLowStock()
    {
        $searchModel = new \core\models\warehouse\ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere('quantity <= min_quantity');

        return $this->render('low_stock', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @inheritdoc
     * @throws \yii\web\BadRequestHttpException
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
     * Finds the Delivery model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Delivery the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Delivery::find()->company()->enabled()->byId($id)->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
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
}
