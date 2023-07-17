<?php

namespace frontend\modules\division\controllers;

use common\components\Model;
use core\forms\division\ServiceCreateForm;
use core\forms\division\ServiceUpdateForm;
use core\forms\ImportForm;
use core\models\company\Company;
use core\models\division\DivisionService;
use core\models\division\DivisionServiceInsuranceCompany;
use core\models\division\DivisionServiceProduct;
use core\models\ServiceCategory;
use core\services\division\ServiceModelService;
use frontend\modules\division\search\DivisionServiceSearch;
use Yii;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * DivisionServiceController implements the CRUD actions for DivisionService model.
 */
class ServiceController extends Controller
{
    private $service;

    /**
     * ServiceController constructor.
     * @param string $id
     * @param Module $module
     * @param ServiceModelService $service
     * @param array $config
     */
    public function __construct($id, Module $module, ServiceModelService $service, array $config = [])
    {
        $this->service = $service;

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
                            'update',
                            'delete',
                            'formula',
                            'template',
                            'search',
                            'restore',
                            'list'
                        ],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                    [
                        'actions' => ['index', 'archive', 'export'],
                        'allow'   => true,
                        'roles'   => ['divisionServiceView'],
                    ],
                    [
                        'actions' => ['create', 'import', 'process'],
                        'allow'   => true,
                        'roles'   => ['divisionServiceCreate'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'restore' => ['POST']
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
            $this->getView()->params['bodyID'] = 'service';
            $this->getView()->params['sideNavView'] = 'tree';
            $this->getView()->params['sideNavID'] = 'services';
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
            'label'  => Yii::t('app', 'Categories'),
            'icon'   => 'icon sprite-filter_purchased_services',
            'url'    => ['service/index'],
            'active' => strpos(Yii::$app->request->url, '/division/service/') !== false,
        ];

        $models = ServiceCategory::getCompanyCategories();

        foreach ($models as $model) {
            $menu[] = [
                'label'  => $model->name,
                'url'    => ['/division/service/index', 'category_id' => $model->id],
                'active' => Yii::$app->request->url == Url::to([
                        '/division/service/index',
                        'category_id' => $model->id
                    ]),
            ];
        }

        return $menu;
    }

    /**
     * Lists all DivisionService models.
     * @param integer $category_id
     * @return mixed
     */
    public function actionIndex($category_id = null)
    {
        $searchModel = new DivisionServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $category_id);
        $dataProvider->query->permitted();

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'category_id'  => $category_id
        ]);
    }

    /**
     * List deleted DivisionService models.
     * @return string
     */
    public function actionArchive()
    {
        $searchModel = new DivisionServiceSearch();
        $searchModel->deleted = true;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('archive', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Creates a new DivisionService model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new ServiceCreateForm();
        $products = [new DivisionServiceProduct()];
        $insuranceCompanies = [new DivisionServiceInsuranceCompany()];

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $products = Model::createMultiple(DivisionServiceProduct::className());
            Model::loadMultiple($products, Yii::$app->request->post());

            $insuranceCompanies = Model::createMultiple(DivisionServiceInsuranceCompany::className());
            Model::loadMultiple($insuranceCompanies, Yii::$app->request->post());

            $valid = Model::validateMultiple($products) && Model::validateMultiple($insuranceCompanies);

            if ($valid) {
                try {
                    $service = $this->service->create($form, $products, $insuranceCompanies);

                    Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));

                    $action = Yii::$app->request->post('action');
                    if ($action == 'add-another') {
                        return $this->redirect(['create']);
                    }

                    return $this->redirect(['index']);
                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model'    => $form,
            'products' => empty($products) ? [new DivisionServiceProduct()] : $products,
            'insuranceCompanies' => empty($insuranceCompanies) ? [new DivisionServiceInsuranceCompany()] : $insuranceCompanies,
        ]);
    }

    /**
     * Updates an existing DivisionService model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("divisionServiceUpdate", ['model' => $model])
        ) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $form = new ServiceUpdateForm($model->id);

        $products = $model->products;
        if (empty($model->products)) {
            $products = [new DivisionServiceProduct()];
        }

        $insuranceCompanies = $model->insuranceCompanies;
        if (empty($insuranceCompanies)) {
            $insuranceCompanies = [new DivisionServiceInsuranceCompany()];
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $products = Model::createMultiple(DivisionServiceProduct::classname(), $products);
            Model::loadMultiple($products, Yii::$app->request->post());

            $insuranceCompanies = Model::createMultiple(DivisionServiceInsuranceCompany::classname(), $insuranceCompanies);
            Model::loadMultiple($insuranceCompanies, Yii::$app->request->post());

            $valid = Model::validateMultiple($products) && Model::validateMultiple($insuranceCompanies);

            if ($valid) {
                try {
                    $service = $this->service->update($id, $form, $products, $insuranceCompanies);

                    $action = Yii::$app->request->post('action');
                    if ($action == 'add-another') {
                        return $this->redirect(['create']);
                    }

                    Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));

                    return $this->refresh();

                } catch (\DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        if (empty($model->products)) {
            $products = [new DivisionServiceProduct()];
        }

        return $this->render('update', [
            'model'    => $form,
            'products' => $products,
            'insuranceCompanies' => $insuranceCompanies
        ]);
    }

    /**
     * Deletes an existing DivisionService model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("divisionServiceDelete", ['model' => $model])) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        try {
            $this->service->delete($id);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful delete {something}',
                ['something' => $model->service_name])
            );
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * @param integer $id Division Service id
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionFormula($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        /* @var Company $company */
        $company = Yii::$app->user->identity->company;

        return [
            'results' => array_map(function (
                DivisionServiceProduct $divisionServiceProduct
            ) use ($company) {
                return [
                    'name'           => $divisionServiceProduct->product->name,
                    'product_id'     => $divisionServiceProduct->product_id,
                    'quantity'       => $divisionServiceProduct->quantity,
                    'unit'           => $divisionServiceProduct->product->unit->name,
                    'price'          => $divisionServiceProduct->product->getPrice(),
                    'stock_level'    => $divisionServiceProduct->product->quantity,
                    'purchase_price' => $divisionServiceProduct->product->purchase_price,
                ];
            }, $model->getProducts()->joinWith(['product.unit'])->all())
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionTemplate()
    {
        $filePath = Yii::$app->getBasePath() . '/web/data/Шаблон_для_импорта_услуг_MYCRM.xlsx';
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('The file does not exists.');
        }

        return Yii::$app->response->sendFile($filePath);
    }

    /**
     * @return string|Response
     * @throws \Exception
     */
    public function actionImport()
    {
        $model = new ImportForm();
        $model->excelFile = UploadedFile::getInstance($model, 'excelFile');

        Yii::$app->session->set('progress', 0);
        if ($model->validate()) {

            Yii::$app->serviceParser->execute($model, Yii::$app->user->identity->company);
            $savedCount = Yii::$app->serviceParser->getSavedCounter();

            return Json::encode([
                'message'   => Yii::t('app', '{number} records were uploaded.', ['number' => $savedCount]),
                'incorrect' => Yii::$app->serviceParser->getIncorrectModels()
            ]);
        }

        Yii::$app->session->setFlash('error', implode('<br/>', array_map(function ($item) {
            return implode('<br/>', $item);
        }, $model->errors)));

        return $this->redirect('index');
    }

    public function actionProcess()
    {
        return Yii::$app->session->get('progress', 0);
    }

    /**
     * @todo replace with api call
     *
     * @param string $name
     *
     * @return array
     */
    public function actionSearch($name = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /* @var DivisionService[] $divisionServices */
        $divisionServices = DivisionService::find()
            ->joinWith(['divisions'])
            ->andFilterWhere(['~*', 'service_name', $name])
            ->andWhere([
                '{{%division_services}}.status' => DivisionService::STATUS_ENABLED,
                '{{%divisions}}.company_id'     => Yii::$app->user->identity->company_id
            ])
            ->limit(10)
            ->all();

        $out = array_map(function (DivisionService $model) {
            return [
                'id'      => $model->id,
                'name'    => $model->getFullName(),
                'options' => [
                    'price'        => $model->price,
                    'duration'     => $model->average_time,
                    'service_name' => $model->service_name
                ]
            ];
        }, $divisionServices);

        return ['results' => $out];
    }

    /**
     * @param null $category_id
     */
    public function actionExport($category_id = null)
    {
        $searchModel = new DivisionServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $category_id);
        $dataProvider->query->permitted();
        $dataProvider->pagination = false;

        $this->service->export($dataProvider);
    }

    /**
     * @param $id
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id, true);
        try {
            $this->service->restore($id);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successful restore {something}',
                ['something' => $model->service_name])
            );
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
     * Search services
     * @return array
     */
    public function actionList()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $params = \Yii::$app->request->post('depdrop_all_params');
        $division = empty($params['division']) ? Yii::$app->user->identity->permittedDivisions: $params['division'];
        $data = ['output' => []];

        if ($division) {
            $items = DivisionService::find()
                ->deleted(false)
                ->joinWith('categories', false)
                ->select([
                    '{{%division_services}}.id',
                    "{{%division_services}}.service_name as name",
                    '{{%service_categories}}.name as category_name'
                ])
                ->division($division, false)
                ->orderBy('{{%service_categories}}.name ASC, service_name ASC')
                ->asArray()
                ->all();

            $data['output'] = ArrayHelper::index($items, null, 'category_name');
        }

        return $data;
    }

    /**
     * Finds the DivisionService model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @param bool $deleted
     *
     * @return array|DivisionService|\yii\db\ActiveRecord
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $deleted = false)
    {
        $model = DivisionService::find()->company(null, false)->deleted($deleted)->byId($id)->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
