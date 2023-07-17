<?php

namespace frontend\modules\warehouse\controllers;

use common\components\Model;
use core\forms\warehouse\usage\UsageCreateForm;
use core\forms\warehouse\usage\UsageUpdateForm;
use core\models\warehouse\Usage;
use core\models\warehouse\UsageHistorySearch;
use core\models\warehouse\UsageProduct;
use core\models\warehouse\UsageSearch;
use core\services\warehouse\dto\UsageDto;
use core\services\warehouse\dto\UsageProductDto;
use core\services\warehouse\UsageService;
use Yii;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * UsageController implements the CRUD actions for Usage model.
 */
class UsageController extends Controller
{
    protected $service;

    public function __construct($id, Module $module, UsageService $usageService, array $config = [])
    {
        $this->service = $usageService;
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
                'only'  => ['index', 'update', 'create', 'cancel'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'cancel', 'batch-cancel'],
                        'allow'   => true,
                        'roles'   => ['warehouseAdmin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'cancel'       => ['POST'],
                    'batch-cancel' => ['POST'],
//                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->getView()->params['bodyID']         = 'stock';
            $this->getView()->params['sideNavView']    = 'list';
            $this->getView()->params['sideNavID']      = 'warehouse';
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
        $menu[] = [
            'label'  => Yii::t('app', 'Usage history'),
            'icon'   => 'icon sprite-stock_history_consumption',
            'url'    => ['usage/index'],
            'active' => strpos(Yii::$app->request->url, 'warehouse/usage') !== false &&
                strpos(Yii::$app->request->url, '/history') === false
        ];
        $menu[] = [
            'label'  => Yii::t('app', 'Statistics'),
            'icon'   => 'icon sprite-stock_history_consumption',
            'url'    => ['usage/history'],
            'active' => strpos(Yii::$app->request->url, 'warehouse/usage/history') !== false
        ];

        return $menu;
    }

    /**
     * Lists all Usage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @return string
     */
    public function actionHistory()
    {
        $searchModel = new UsageHistorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('history', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Usage model.
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
     * Creates a new Usage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $form = new UsageCreateForm();
        $usageProducts = [new UsageProduct()];

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $usageProducts = Model::createMultiple(UsageProduct::classname());
            Model::loadMultiple($usageProducts, Yii::$app->request->post());

            $valid = Model::validateMultiple($usageProducts);

            if ($valid) {
                $productsData = [];
                foreach ($usageProducts as $usageProduct) {
                    $productsData[] = new UsageProductDto(
                        $usageProduct->product_id,
                        $usageProduct->quantity
                    );
                }

                try {
                    $usage = $this->service->create(
                        new UsageDto(
                            Yii::$app->user->identity->company_id,
                            $form->division_id,
                            $form->company_customer_id,
                            $form->staff_id,
                            $form->discount,
                            $form->comments
                        ),
                        $productsData
                    );
                    return $this->redirect(['index']);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model'         => $form,
            'usageProducts' => $usageProducts
        ]);
    }

    /**
     * Updates an existing Usage model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $form = new UsageUpdateForm($id);
        $usageProducts = $model->usageProducts;

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $usageProducts = Model::createMultiple(UsageProduct::classname(), $usageProducts);
            Model::loadMultiple($usageProducts, Yii::$app->request->post());

            $valid = Model::validateMultiple($usageProducts);

            if ($valid) {
                $productsData = [];
                foreach ($usageProducts as $usageProduct) {
                    $productsData[] = new UsageProductDto(
                        $usageProduct->product_id,
                        $usageProduct->quantity,
                        $usageProduct->id ?? null
                    );
                }


                try {
                    $usage = $this->service->update(
                        $id,
                        new UsageDto(
                            Yii::$app->user->identity->company_id,
                            $form->division_id,
                            $form->company_customer_id,
                            $form->staff_id,
                            $form->discount,
                            $form->comments
                        ),
                        $productsData
                    );
                    return $this->redirect(['index']);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('update', [
            'model'         => $form,
            'usageProducts' => (empty($usageProducts)) ? [new UsageProduct] : $usageProducts
        ]);
    }

    /**
     * Deletes an existing Usage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionCancel($id)
    {
        try {
            $this->findModel($id);
            $this->service->cancel($id);
            Yii::$app->session->setFlash('success', 'Возврат произведен успешно');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * @return array
     */
    public function actionBatchCancel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $selected = Yii::$app->request->getBodyParam('selected');

        $data = ['deleted' => 0, 'errors' => []];
        if ($selected) {
            foreach ($selected as $id) {
                try {
                    $this->service->cancel($id);
                    $data['deleted']++;
                } catch (\DomainException $e) {
                    $data['errors'][] = "Ошибка при отмене Списания #{$id}: " . $e->getMessage();
                }
            }
        }

        return $data;
    }

    /**
     * Deletes an existing Usage model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
//        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Usage model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Usage the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Usage::find()->company()->byId($id)->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
