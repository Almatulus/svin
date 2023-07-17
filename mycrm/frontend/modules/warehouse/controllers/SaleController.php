<?php

namespace frontend\modules\warehouse\controllers;

use common\components\Model;
use core\forms\warehouse\SaleAnalysisForm;
use core\forms\warehouse\SaleForm;
use core\forms\warehouse\SaleUpdateForm;
use core\models\warehouse\Sale;
use core\models\warehouse\SaleProduct;
use core\models\warehouse\SaleSearch;
use core\services\warehouse\SaleService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * SaleController implements the CRUD actions for Sale model.
 */
class SaleController extends Controller
{
    private $saleService;

    public function __construct($id, $module, SaleService $saleService, $config = [])
    {
        $this->saleService = $saleService;
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
                'only'  => ['index', 'update', 'create', 'delete', 'analysis', 'export-analysis'],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'create',
                            'update',
                            'delete',
                            'analysis',
                            'export-analysis',
                            'batch-delete'
                        ],
                        'allow'   => true,
                        'roles'   => ['warehouseAdmin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'batch-delete' => ['POST'],
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
        $menu = [
            [
                'label' => Yii::t('app', 'Sales history'),
                'icon' => 'icon sprite-stock_sale_history',
                'url' => ['sale/index'],
                'active' => strpos(Yii::$app->request->url, 'warehouse/sale') !== false
                    && strpos(Yii::$app->request->url, 'analysis') === false
            ],
            [
                'label' => Yii::t('app', 'Sales analysis'),
                'icon' => 'icon sprite-stock_sale_history',
                'url' => ['sale/analysis']
            ],
        ];

        return $menu;
    }

    /**
     * Lists all Sale models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new SaleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Sale model.
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
     * Creates a new Sale model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $form         = new SaleForm();
        $saleProducts = [new SaleProduct()];

        if ($form->load(Yii::$app->request->post())) {

            $saleProducts = Model::createMultiple(SaleProduct::classname());
            Model::loadMultiple($saleProducts, Yii::$app->request->post());

            // validate all models
            $valid = $form->validate();
            $valid = Model::validateMultiple($saleProducts) && $valid;

            if ($valid) {
                try {
                    $model = $this->saleService->create(
                        $form->cash_id,
                        $form->company_customer_id,
                        $form->discount,
                        $form->division_id,
                        $form->paid,
                        $form->payment_id,
                        $form->sale_date,
                        $form->staff_id,
                        $saleProducts
                    );
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                    return $this->redirect(['index']);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model' => $form,
            'saleProducts' => $saleProducts
        ]);
    }

    /**
     * Updates an existing Sale model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model        = $this->findModel($id);
        $form         = new SaleUpdateForm($model);
        $saleProducts = $model->saleProducts;

        if ($form->load(Yii::$app->request->post())) {

            $oldIDs       = ArrayHelper::map($saleProducts, 'id', 'id');
            $saleProducts = Model::createMultiple(SaleProduct::classname(), $saleProducts);
            Model::loadMultiple($saleProducts, Yii::$app->request->post());
            $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($saleProducts, 'id', 'id')));

            // validate all models
            $valid = $form->validate();
            $valid = Model::validateMultiple($saleProducts) && $valid;

            if ($valid) {
                try {
                    $this->saleService->edit(
                        $id,
                        $form->cash_id,
                        $form->company_customer_id,
                        $form->discount,
                        $form->division_id,
                        $form->paid,
                        $form->payment_id,
                        $form->sale_date,
                        $form->staff_id,
                        $deletedIDs,
                        $saleProducts
                    );
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                    return $this->redirect(['index']);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('update', [
            'model'        => $form,
            'saleProducts' => (empty($saleProducts)) ? [new SaleProduct] : $saleProducts
        ]);
    }

    /**
     * Deletes an existing Sale model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        try {
            $this->saleService->delete($id);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful deleted'));
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['index']);
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
            foreach ($selected as $id) {
                try {
                    $this->saleService->delete($id);
                    $data['deleted']++;
                } catch (\DomainException $e) {
                    $data['errors'][] = "Ошибка при удалении Продажи #{$id}: " . $e->getMessage();
                }
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function actionAnalysis()
    {
        $model = new SaleAnalysisForm();
        $model->load(Yii::$app->request->get());

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $model->getProviderQuery()
        ]);

        return $this->render('analysis', [
            'dataProvider' => $dataProvider,
            'model' => $model
        ]);
    }

    /**
     * Exports excel file
     */
    public function actionExportAnalysis()
    {
        $model = new SaleAnalysisForm();
        $model->load(Yii::$app->request->get());

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query'      => $model->getProviderQuery(),
            'pagination' => false
        ]);

        try {
            $this->saleService->export($dataProvider->getModels());
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', Yii::t('app', $e->getMessage()));
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the Sale model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Sale the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Sale::find()->company()->byId($id)->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
