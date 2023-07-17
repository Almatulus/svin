<?php

namespace frontend\modules\warehouse\controllers;

use core\forms\ImportForm;
use core\forms\warehouse\product\ProductCreateForm;
use core\forms\warehouse\product\ProductUpdateForm;
use core\models\company\Company;
use core\models\warehouse\Category;
use core\models\warehouse\Product;
use core\models\warehouse\ProductSearch;
use core\services\warehouse\ProductService;
use Exception;
use Yii;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    protected $service;

    /**
     * ProductController constructor.
     * @param string $id
     * @param Module $module
     * @param ProductService $service
     * @param array $config
     */
    public function __construct($id, Module $module, ProductService $service, array $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->service = $service;
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
                            'create',
                            'update',
                            'delete',
                            'batch-delete',
                            'export',
                            'process',
                            'import',
                            'archive',
                            'restore'
                        ],
                        'allow'   => true,
                        'roles'   => ['warehouseAdmin'],
                    ],
                    [
                        'actions' => ['search', 'template', 'list'],
                        'allow'   => true,
                        'roles'   => ['@']
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*']
                    ]
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'restore' => ['POST'],
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
            $this->getView()->params['bodyID'] = 'stock';
            $this->getView()->params['sideNavView'] = 'tree';
            $this->getView()->params['sideNavID'] = 'warehouse';
            $this->getView()->params['sideNavOptions'] = self::getPanelItems();

            return true;
        }

        return false;
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
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
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new ProductCreateForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->create($form);

                Yii::$app->session->setFlash('success', Yii::t('app', 'Product added'));
                $action = Yii::$app->request->post('action');
                if ($action == 'add-another') {
                    return $this->redirect(['create']);
                }
                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new ProductUpdateForm($id);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->update($model->id, $form);

                $action = Yii::$app->request->post('action');
                if ($action == 'add-another') {
                    return $this->redirect(['create']);
                }
                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $this->findModel($id);
        $this->service->remove($id);

        return $this->redirect(['index']);
    }

    /**
     * @throws Exception
     */
    public function actionBatchDelete()
    {
        $products = Yii::$app->request->post('products');
        foreach ($products as $product){
            $this->findModel($product);
        }
        $this->service->batchRemove($products);
    }

    /**
     * @param bool $all
     */
    public function actionExport($all = false)
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if ($all) {
            $dataProvider->pagination->pageSize = 0;
        }
        $dataProvider->query->orderBy('category_id');
        $searchModel->export($dataProvider->models);
    }

    /**
     * @param null $search
     * @param null $id
     * @return array
     */
    public function actionSearch($search = null, $id = null)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        /* @var Company $company */
        $company = Yii::$app->user->identity->company;
        if (!is_null($search)) {
            $products = Product::find()
                ->where([
                    'OR',
                    ['~*', 'crm_warehouse_product.name', $search],
                    ['like', 'barcode', $search],
                    ['like', 'sku', $search]
                ])
                ->active()
                ->company()
                ->permitted()
                ->joinWith(['unit'])
                ->orderBy('{{%warehouse_product}}.name')
                ->limit(50)
                ->all();

            $out['results'] = array_values(array_map(function (Product $product) use ($company) {
                return [
                    'id'             => $product->id,
                    'text'           => $product->name,
                    'unit'           => $product->unit->name,
                    "price"          => $product->getPrice(),
                    "purchase_price" => $product->purchase_price,
                    'vat'            => $product->vat,
                    'stock_level'    => $product->quantity
                ];
            }, $products));
        } elseif ($id > 0) {
            $product = Product::find()->active()->company()->andWhere(['id' => $id])->one();
            $out['results'] = [
                'id'          => $id,
                'text'        => $product->name,
                'unit'        => $product->unit->name,
                'price'       => $product->getPrice(),
                'vat'         => $product->vat,
                'stock_level' => $product->quantity
            ];
        }
        return $out;
    }

    /**
     * @return $this
     * @throws NotFoundHttpException
     */
    public function actionTemplate()
    {
        $filePath = Yii::$app->getBasePath() . '/web/data/Шаблон_для_импорта_товаров_MYCRM.xlsx';
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exists.');
        }
    }

    /**
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionImport()
    {
        $model = new ImportForm();
        $model->excelFile = UploadedFile::getInstance($model, 'excelFile');

        Yii::$app->session->set('progress', 0);
        if ($model->validate()) {

            Yii::$app->productParser->execute($model, Yii::$app->user->identity->company);
            $savedCount = Yii::$app->productParser->getSavedCounter();

            return Json::encode([
                'message'   => Yii::t('app', '{number} records were uploaded.', ['number' => $savedCount]),
                'incorrect' => Yii::$app->productParser->getIncorrectModels()
            ]);
        }

        Yii::$app->session->setFlash('error', implode('<br/>', array_map(function ($item) {
            return implode('<br/>', $item);
        }, $model->errors)));

        return $this->redirect('index');
    }

    /**
     * @return mixed
     */
    public function actionProcess()
    {
        return Yii::$app->session->get('progress', 0);
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param int $status
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $status = Product::STATUS_ENABLED)
    {
        $model = Product::find()->status($status)->company()->byId($id)->one();
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
        $menu[] = [
            'label'  => Yii::t('app', 'All Products'),
            'icon'   => 'icon sprite-stock_products',
            'url'    => ['product/index'],
            'active' => strpos(Yii::$app->request->url, 'warehouse/product') !== false &&
                strpos(Yii::$app->request->url, 'category_id') === false,
        ];

        $models = Category::getCompanyCategories();

        foreach ($models as $key => $model) {
            $menu[] = [
                'label'  => $model->name,
                'url'    => ['index', 'ProductSearch[category_id]' => $model->id],
                'active' => strpos(Yii::$app->request->url, '[category_id]=' . $model->id),
            ];
        }

        return $menu;
    }

    public function actionArchive()
    {
        $searchModel = new ProductSearch();
        $searchModel->status = Product::STATUS_DISABLED;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('archive', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id, Product::STATUS_DISABLED);

        try {
            $this->service->restore($id);
            return $this->redirect(['index']);
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        $url = Yii::$app->request->referrer;
        if (!$url) {
            $url = ['view', 'id' => $id];
        }

        return $this->redirect($url);
    }

    /**
     * Search categories
     * @return array
     */
    public function actionList()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $params = \Yii::$app->request->post('depdrop_all_params');
        $division = $params['division_id'];
        $data = ['output' => []];

        if ($division) {
            $items = Product::find()
                ->active()
                ->select([
                    '{{%warehouse_product}}.id',
                    "{{%warehouse_product}}.name",
                    '{{%warehouse_category}}.name as category_name'
                ])
                ->joinWith('category', false)
                ->division($division)
                ->orderBy('{{%warehouse_category}}.name ASC, name ASC')
                ->asArray()
                ->all();

            $data['output'] = ArrayHelper::index($items, null, 'category_name');
        }

        return $data;
    }
}
