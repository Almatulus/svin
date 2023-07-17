<?php

namespace frontend\modules\warehouse\controllers;

use common\components\Model;
use core\forms\warehouse\stocktake\StocktakeCreateForm;
use core\models\warehouse\Product;
use core\models\warehouse\Stocktake;
use core\models\warehouse\StocktakeProduct;
use core\models\warehouse\StocktakeSearch;
use core\repositories\exceptions\InsufficientStockLevel;
use core\services\warehouse\StocktakeService;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * StocktakeController implements the CRUD actions for Stocktake model.
 */
class StocktakeController extends Controller
{
    private $service;

    public function __construct($id, $module, StocktakeService $stocktakeService, $config = [])
    {
        $this->service = $stocktakeService;
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
                'only'  => [
                    'index',
                    'update',
                    'create',
                    'view',
                    'delete',
                    'edit-products',
                    'summary',
                    'execute',
                    'delete'
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'create',
                            'view',
                            'update',
                            'delete',
                            'edit-products',
                            'summary',
                            'execute',
                            'delete'
                        ],
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
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->view->params['bodyClass'] = 'no_sidenav';

        return parent::beforeAction($action);
    }


    /**
     * Lists all Stocktake models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StocktakeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['status' => Stocktake::STATUS_COMPLETED]);
        $currentStocktake = $this->service->getCurrent();

        return $this->render('index', [
            'searchModel'      => $searchModel,
            'dataProvider'     => $dataProvider,
            'currentStocktake' => $currentStocktake
        ]);
    }

    /**
     * Displays a single Stocktake model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id, false),
        ]);
    }

    /**
     * Creates a new Stocktake model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $currentStocktake = $this->service->getCurrent();
        if ($currentStocktake) {
            Yii::$app->session->setFlash('error',
                Yii::t('app', 'It is necessary to complete the current stocktake!'));
            return $this->redirect(['index']);
        }

        $form = new StocktakeCreateForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $model = $this->service->create(
                    $form->type_of_products,
                    $form->category_id,
                    $form->name,
                    $form->division_id,
                    $form->creator_id,
                    $form->description
                );

                return $this->redirect(['edit-products', 'id' => $model->id]);
            }catch (InsufficientStockLevel $e){
                Yii::$app->session->setFlash('error', $e->getMessage());
            }catch (\DomainException $e){
                Yii::$app->session->setFlash('error', $e->getMessage());
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'form' => $form,
            'model' => new Stocktake()
        ]);
    }

    /**
     * Updates an existing Stocktake model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['edit-products', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Stocktake model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionEditProducts($id)
    {
        $stocktake = $this->findModel($id);

        $stocktakeProducts = $this->service->getProducts($id);

        if (Yii::$app->request->isPost) {

            $stocktakeProducts = $stocktake->hasProducts() ? $stocktakeProducts : [];
            $stocktakeProducts = Model::createMultiple(StocktakeProduct::classname(), $stocktakeProducts);

            // load and validate all models
            Model::loadMultiple($stocktakeProducts, Yii::$app->request->post());

            $valid = Model::validateMultiple($stocktakeProducts, [
                'product_id',
                'actual_stock_level'
            ]);

            if ($valid) {
                try {
                    $this->service->editProducts($stocktake->id, $stocktakeProducts);

                    return $this->redirect(['summary', 'id' => $stocktake->id]);
                } catch (Exception $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('edit_products', [
            'model'    => $stocktake,
            'products' => $stocktakeProducts
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionSummary($id)
    {
        $model = $this->findModel($id);
        $stocktakeProducts = $model->changedProducts;

        if (Yii::$app->request->isPost) {
            // load and validate all models
            $stocktakeProducts = Model::createMultiple(StocktakeProduct::classname(), $stocktakeProducts);
            Model::loadMultiple($stocktakeProducts, Yii::$app->request->post());
            $valid = Model::validateMultiple($stocktakeProducts);

            if ($valid) {
                try {
                    $this->service->updateProductsQuantity($model->id, $stocktakeProducts);

                    return $this->redirect(['view', 'id' => $model->id]);
                } catch (Exception $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('summary', [
            'model'    => $model,
            'products' => $stocktakeProducts
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionExecute($id)
    {
        $model = $this->findModel($id);
        $this->service->complete($id);
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Stocktake model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param bool $notCompleted
     * @return Stocktake the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $notCompleted = true)
    {
        $query = Stocktake::find()->company()->permitted()->byId($id);
        if ($notCompleted) {
            $query->andWhere(['<>', 'status', Stocktake::STATUS_COMPLETED]);
        }
        $model = $query->one();

        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
