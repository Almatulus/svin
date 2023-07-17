<?php

namespace frontend\modules\customer\controllers;

use common\components\parsers\CustomerParser;
use core\forms\customer\CompanyCustomerCreateForm;
use core\forms\customer\CompanyCustomerPayDebtForm;
use core\forms\customer\CompanyCustomerUpdateForm;
use core\forms\customer\CustomerForm;
use core\forms\customer\MergeForm;
use core\forms\ImportForm;
use core\helpers\company\PaymentHelper;
use core\helpers\customer\CustomerHelper;
use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerRequest;
use core\models\File;
use core\models\order\Order;
use core\models\order\OrderDocument;
use core\models\Service;
use core\services\customer\CompanyCustomerService;
use core\services\dto\CustomerData;
use core\services\dto\CustomerInsuranceData;
use frontend\modules\customer\components\CustomerModuleController;
use frontend\modules\order\search\OrderSearch;
use frontend\search\CustomerSearch;
use PHPExcel_Exception;
use PHPExcel_Reader_Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends CustomerModuleController
{
    private $companyCustomerService;

    public function __construct($id, $module, CompanyCustomerService $companyCustomerService, $config = [])
    {
        $this->companyCustomerService = $companyCustomerService;
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
                        'actions' => [
                            'edit',
                            'update',
                            'delete',
                            'delete-customers',
                            'info',
                            'visits',
                            'new',
                            'pay-debt',
                            'archive',
                            'restore',
                            'merge',
                            'user-list'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'view', 'index', 'order-history', 'request-history',
                            'services', 'export',
                            'add-categories',
                            'send-request',
                            'process',
                            'template', 'history'
                        ],
                        'allow' => true,
                        'roles' => ['companyCustomerView'],
                    ],
                    [
                        'actions' => ['create', 'import'],
                        'allow' => true,
                        'roles' => ['companyCustomerCreate'],
                    ],
                    [
                        'actions' => ['lost', 'lost-export'],
                        'allow'   => true,
                        'roles'   => ['companyCustomerLostView']
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete'           => ['post'],
                    'delete-customers' => ['post'],
                    'import'           => ['post'],
                    'merge'            => ['post']
                ],
            ],
        ];
    }

    /**
     * Updates an existing Customer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $companyCustomer = $this->findModel($id);

        if (!Yii::$app->user->can("companyCustomerUpdate", ["model" => $companyCustomer])) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $form = new CompanyCustomerUpdateForm($companyCustomer);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $imageFile = UploadedFile::getInstance($form, 'imageFile');

                $this->companyCustomerService->updateProfile(
                    new CustomerData(
                        $companyCustomer->id,
                        $form->name,
                        $form->lastname,
                        $form->patronymic,
                        $form->phone,
                        $form->source_id,
                        $form->medical_record_id
                    ),
                    $form->email,
                    $form->gender,
                    $form->birth_date,
                    $form->address,
                    $form->city,
                    $form->categories,
                    $form->comments,
                    $form->sms_birthday,
                    $form->sms_exclude,
                    intval($form->balance),
                    $form->employer,
                    $form->job,
                    $form->iin,
                    $form->id_card_number,
                    $imageFile,
                    $form->discount,
                    $form->cashback_percent,
                    new CustomerInsuranceData(
                        $form->insurance_company_id,
                        $form->insurance_expire_date ? new \DateTime($form->insurance_expire_date) : null,
                        $form->insurance_policy_number,
                        $form->insurer
                    ),
                    $form->phones
                );
                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', Yii::t('app', $e->getMessage()));
            }
            return $this->redirect(['view', 'id' => $companyCustomer->id]);
        }

        return $this->render('update', [
            'model' => $form,
        ]);
    }

    /**
     * Lists all Customer models and advanced search functionality.
     *
     * fetchedCustomers - IDs of all fetched customers with search class. It will contain all ID's not depending on
     * page size of DataProvider.
     *
     * @return mixed
     */
    public function actionIndex()
    {
//        if ( ! Yii::$app->user->isGuest) {
//            /* @var Company $company */
//            $company = Yii::$app->user->identity->company;
//            $should_goto_new_design = $company->show_new_interface && ! empty(Yii::$app->params['vue_host']);
//            if ($should_goto_new_design) {
//                return $this->gotoNewDesign();
//            }
//        }

        $searchModel = new CustomerSearch();
        $params = Yii::$app->request->queryParams;

        // save search params in session, in case to export the data through actionExport
        Yii::$app->session->set('customer_search_query', $params);

        $dataProvider = $searchModel->search($params);
        $dataProvider->setPagination([
            'route' => '/customer/customer/index'
        ]);

        $allProvider = clone $dataProvider;
        $allProvider->setPagination([
            'pageParam' => '',
            'pageSizeParam' => '',
        ]);
        $fetchedCustomers = ArrayHelper::getColumn($allProvider->getModels(), 'id');

        // set params for filters
        $this->view->params['renderForm'] = true;
        $this->view->params['formOptions'] = [
            'action' => ['index'],
            'method' => 'get',
            'id' => 'js-activeform',
            'options' => ['data-pjax' => true],
        ];
        $this->view->params['formViewUrl'] = '/customer/_search';
        $this->view->params['formModel'] = $searchModel;

        Yii::$app->session->set('progress', 0);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'fetchedCustomers' => $fetchedCustomers,
        ]);
    }

    /**
     * Lists all Customer models and advanced search functionality.
     *
     * fetchedCustomers - IDs of all fetched customers with search class. It will contain all ID's not depending on
     * page size of DataProvider.
     *
     * @return mixed
     */
    public function actionArchive()
    {
        $searchModel = new CustomerSearch();
        $searchModel->active = false;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('archive', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Customer model.
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->setCustomerSidebarOptions($model);

        $orders_passed = Order::find()
            ->passed()
            ->finished()
            ->companyCustomer($model)
            ->orderDatetime(SORT_DESC)
            ->all();
        $orders_soon = Order::find()
            ->future()
            ->enabled()
            ->companyCustomer($model)
            ->orderDatetime(SORT_DESC)
            ->all();

        $documents = OrderDocument::find()
            ->leftJoin('{{%orders}}', '{{%orders}}.id = order_id')
            ->leftJoin('{{%company_customers}}', '{{%company_customers}}.id = company_customer_id')
            ->where(['company_customer_id' => $id])
            ->limit(20)
            ->all();

        $files = File::find()
            ->innerJoin('{{%order_files}}', '{{%order_files}}.file_id = {{%s3_files}}.id')
            ->leftJoin('{{%orders}}', '{{%orders}}.id = order_id')
            ->leftJoin('{{%company_customers}}', '{{%company_customers}}.id = company_customer_id')
            ->where(['company_customer_id' => $id])
            ->limit(20)
            ->all();

        return $this->render('view', [
            'orders_passed' => $orders_passed,
            'orders_soon' => $orders_soon,
            'model' => $model,
            'documents' => $documents,
            'files' => $files
        ]);
    }

    /**
     * Creates a new Customer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new CompanyCustomerCreateForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $imageFile = UploadedFile::getInstance($form, 'imageFile');

                $companyCustomer = $this->companyCustomerService->createCustomer(
                    new CustomerData(
                        null,
                        $form->name,
                        $form->lastname,
                        $form->patronymic,
                        $form->phone,
                        $form->source_id,
                        $form->medical_record_id
                    ),
                    $form->email,
                    $form->gender,
                    $form->birth_date,
                    $form->address,
                    $form->city,
                    $form->categories,
                    $form->comments,
                    $form->sms_birthday,
                    $form->sms_exclude,
                    intval($form->balance),
                    Yii::$app->user->getIdentity()->company_id,
                    $form->employer,
                    $form->job,
                    $form->iin,
                    $form->id_card_number,
                    $imageFile,
                    $form->discount,
                    $form->cashback_percent,
                    new CustomerInsuranceData(
                        $form->insurance_company_id,
                        $form->insurance_expire_date ? new \DateTime($form->insurance_expire_date) : null,
                        $form->insurance_policy_number,
                        $form->insurer
                    ),
                    $form->phones
                );

                Yii::$app->session->setFlash('success', Yii::t('app', 'Successful saving'));
                $this->redirect(['view', 'id' => $companyCustomer->id]);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
                $this->redirect(['create']);
            }
        }

        return $this->render('create', [
            'model' => $form,
        ]);
    }

    /**
     * Deletes an existing Customer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("companyCustomerDelete", ["model" => $model])) {
            throw new ForbiddenHttpException('The requested page does not exist.');
        }

        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Customers batch delete
     */
    public function actionDeleteCustomers()
    {
        $company_customers = Yii::$app->request->post('customers');
        if (is_array($company_customers)) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($company_customers as $company_customer_id) {
                    $model = $this->findModel($company_customer_id);
                    if (Yii::$app->user->can("companyCustomerDelete", ['model' => $model])) {
                        $model->is_active = false;
                        if (!$model->save()) {
                            throw new Exception("Error while updating");
                        }
                    }
                }

                $transaction->commit();
                echo 'success';
            } catch (\Exception $e) {
                $transaction->rollBack();
                echo 'error';
            }
        }
    }

    /**
     * Pay customer's debt
     *
     * @param integer $id
     *
     * @return CompanyCustomer
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionPayDebt($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        $form = new CompanyCustomerPayDebtForm($model);
        $form->load(Yii::$app->request->getQueryParams());

        if (!$form->validate()) {
            $errors = $form->getErrors();
            throw new \RuntimeException(Yii::t('app', reset($errors)[0]));
        }

        return $this->companyCustomerService->payDebt($model->id, [PaymentHelper::CASH_ID => $form->amount],
            Yii::$app->user->id, new \DateTime());
    }


    /**
     * Action is displayed in Customer Profile info. Shows the list of all orders, that was made by the Customer
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionOrderHistory($id)
    {
        $company_customer = $this->findModel($id);

        $searchModel  = new OrderSearch();
        $searchModel->company_customer_id = $company_customer->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('profile/order_history', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Action is displayed in Customer Profile info. Shows the list of all Requests(SMS), which were send to the Customer
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionRequestHistory($id)
    {
        $this->findModel($id);

        $query = CustomerRequest::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $query->where(['customer_id' => $id]);
        $query->orderBy('created_time DESC');

        return $this->render('profile/request_history', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Returns list of services by query search
     *
     * @param null $q
     * @param null $id
     *
     * @return array
     * @throws Exception
     */
    public function actionServices($q = null, $id = null)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new Query;
            $query->select('id, name AS text')
                ->from('{{%services}}')
                ->where(['like', 'name', $q])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Service::findOne(['id' => $id])->name];
        }
        return $out;
    }

    /**
     * Returns file with total stats
     * @param int $mode
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function actionExport($mode = 0)
    {
        $search_params = intval($mode) === 1 ? null : Yii::$app->session->get('customer_search_query');

        $searchModel = new CustomerSearch();
        $dataProvider = $searchModel->search($search_params);
        // $dataProvider->query->with(['customer']); more memory usage, less SQL queries
        $dataProvider->pagination = false;

        $this->companyCustomerService->export($dataProvider);
    }

    /**
     * for Ajax. Adds multiple Categories to multiple Customers.
     * POST[customers] - list of Customers, who will be assigned with Categories
     * POST[categories] - list of assigning Categories
     *
     * prints 'success' or 'error' depending on results
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \yii\base\ExitException
     */
    public function actionAddCategories()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new NotFoundHttpException();
        }

        $company_customer_ids = $request->post('customers');
        $category_ids = $request->post('categories');
        if (is_array($company_customer_ids) && is_array($category_ids)) {
            $company_customers = CompanyCustomer::find()->where(['id' => $company_customer_ids])->indexBy('id')->all();
            try {
                foreach ($company_customers as $company_customer) {
                    $this->companyCustomerService->addCategories($company_customer->id, $category_ids);
                }
                echo 'success';
            } catch (\DomainException $e) {
                echo 'error';
            }
        }
        Yii::$app->end();
    }

    /**
     * TODO check Action
     * for Ajax. Adds multiple Categories to multiple Customers.
     * POST[customers] - list of Customers, who will be assigned with Categories
     * POST[categories] - list of assigning Categories
     *
     * prints 'success' or 'error' depending on results
     */
    public function actionSendRequest()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->isAjax) {

            $all = $request->post('all');
            $companyCustomerIDs = $request->post('customers');
            $message = $request->post('message');

            $company = Yii::$app->user->identity->company;
            $query = CompanyCustomer::find()->company($company->id);
            if ($all == 'true') {
                $query = $query->active(true);
            } else {
                $query = $query->andWhere(['IN', 'id', $companyCustomerIDs]);
            }
            $companyCustomers = $query->all();

            try {
                if($this->companyCustomerService->sendRequest($companyCustomers, $company, $message)) {
                    return [
                        'status' => 'success',
                        'message' => 'SMS успешно отправлены',
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Произошла ошибка'
        ];
    }

    /**
     * Finds the Customers based on its name or surname.
     * If the model is not found, an empty array is returne.
     * @param string $q
     * @param integer $id
     * @param int $phone
     * @return array $out
     */
    public function actionUserList($q = null, $id = null, $phone = 0)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = CompanyCustomer::find()
                ->joinWith(['customer'], false)
                ->select(['{{%company_customers}}.id', "CONCAT(lastname, ' ', name, ' ', phone) as text"])
                ->company()
                ->active(true)
                ->andWhere([
                    'or',
                    ['like', 'phone', $q],
                    ['~*', "CONCAT(lastname, ' ', name, ' ', patronymic)", $q]
                ]);

            if ($phone) {
                $query->andWhere("phone IS NOT NULL AND phone <> ''");
            }

            $query->orderBy('lastname ASC')
                ->limit(30)
                ->asArray();

            $out['results'] = array_values($query->all());
        } elseif ($id > 0) {
            $customer = Customer::findOne($id);
            $out['results'] = ['id' => $id, 'text' => $customer->lastname . " " . $customer->name];
        }
        return $out;
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionTemplate()
    {
        $filePath = Yii::$app->getBasePath() . '/web/data/Шаблон загрузки.xlsx';
        if ( ! file_exists($filePath)) {
            throw new NotFoundHttpException('The file does not exists.');
        }

        return Yii::$app->response->sendFile($filePath);
    }

    /**
     * @return array|Response
     * @throws Exception
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function actionImport()
    {
        $model = new ImportForm();
        $model->excelFile = UploadedFile::getInstance($model, 'excelFile');

        if ( ! $model->validate()) {
            $errors = $model->getErrors();
            Yii::$app->session->setFlash('error', reset($errors)[0]);
            return $this->redirect('index');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $savedCount                 = CustomerParser::parse(
            $model,
            Yii::$app->user->identity->company
        );

        return [
            'message' => Yii::t(
                'app',
                '{number} clients were uploaded',
                ['number' => $savedCount]
            ),
        ];
    }

    /**
     * @return mixed
     */
    public function actionProcess()
    {
        return Yii::$app->session->get('progress', 0);
    }

    /**
     * Returns customer info
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionInfo($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return Yii::createObject('yii\rest\Serializer')->serialize($this->findModel($id));
    }

    /**
     * Returns customer visits info
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionVisits($id)
    {
        $model = $this->findModel($id);
        $orders_passed = Order::find()
            ->passed()
            ->finished()
            ->companyCustomer($model)
            ->orderDatetime(SORT_DESC)
            ->limit(10)
            ->all();
        return $this->renderPartial("_visits", ['orders_passed' => $orders_passed]);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionNew()
    {
        $model = new Customer();
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $dynamicModel = \yii\base\DynamicModel::validateData($model->attributes);
                $dynamicModel->addRule('phone', 'required');
                $dynamicModel->addRule('gender', 'integer');
                $dynamicModel->addRule('phone', 'match', ['pattern' => CustomerHelper::PHONE_VALIDATE_PATTERN]);
                $dynamicModel->addRule(['phone', 'name', 'lastname'], 'string', ['max' => 255]);

                if ($dynamicModel->validate()) {
                    $customer = Customer::getCustomer($model->phone, $model->name, $model->email);
                    $customer->gender = $model->gender;
                    $customer->save();

                    $companyCustomer = CompanyCustomer::find()->company()->customer($customer->id)->one();
                    if (!$companyCustomer) {
                        $companyCustomer = new CompanyCustomer();
                        $companyCustomer->customer_id = $customer->id;
                        $companyCustomer->discount = 0;
                        $companyCustomer->sms_birthday = true;
                        $companyCustomer->sms_exclude = false;
                        $companyCustomer->company_id = Yii::$app->user->identity->company_id;
                        $companyCustomer->is_active = true;

                        if ($companyCustomer->save()) {
                            return \yii\helpers\Json::encode(['status' => "success"]);
                        } else {
                            return \yii\helpers\Json::encode(['status' => "error"]);
                        }
                    }
                    return \yii\helpers\Json::encode(['status' => "success"]);
                } else {
                    return \yii\helpers\Json::encode(["errors" => $dynamicModel->errors]);
                }
            }
        }
        return $this->renderAjax('new', ['model' => $model]);
    }

    /**
    * Shows lost customers
    */
    public function actionLost() {

        $model = new CustomerForm();
        $model->load(Yii::$app->request->get());

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery(),
        ]);

        return $this->render('lost_customers', compact('model', 'dataProvider'));
    }

    /**
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    public function actionLostExport() {
        $model = new CustomerForm();
        $model->load(Yii::$app->request->get());

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getQuery(),
        ]);
        $dataProvider->pagination = false;

        $this->companyCustomerService->exportLost($dataProvider);
    }

    /**
     * Shows customer history
     *
     * @param integer $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionHistory($id)
    {
        $model = $this->findModel($id);
        $this->setCustomerSidebarOptions($model);

        $searchModel                      = new OrderSearch();
        $searchModel->status              = OrderConstants::STATUS_FINISHED;
        $searchModel->company_customer_id = $model->id;

        $dataProvider
            = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('history', [
            'dataProvider' => $dataProvider,
            'model'        => $model
        ]);
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionEdit()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($_POST['company_customer_id'])) {
            $companyCustomer = $this->findModel($_POST['company_customer_id']);

            if (!Yii::$app->user->can("companyCustomerUpdate", ["model" => $companyCustomer])) {
                throw new ForbiddenHttpException('Not allowed');
            }

            $form = new CompanyCustomerUpdateForm($companyCustomer);

            if ($form->load(Yii::$app->request->post())) {
                if ($form->validate()) {
                    $imageFile = UploadedFile::getInstance($form, 'imageFile');

                    $model = $this->companyCustomerService->updateProfile(
                        new CustomerData(
                            $companyCustomer->id,
                            $form->name,
                            $form->lastname,
                            $form->patronymic,
                            $form->phone,
                            $form->source_id,
                            $form->medical_record_id
                        ),
                        $form->email,
                        $form->gender,
                        $form->birth_date,
                        $form->address,
                        $form->city,
                        $form->categories,
                        $form->comments,
                        $form->sms_birthday,
                        $form->sms_exclude,
                        $companyCustomer->balance,
                        $form->employer,
                        $form->job,
                        $form->iin,
                        $form->id_card_number,
                        $imageFile,
                        $form->discount,
                        $form->cashback_percent,
                        new CustomerInsuranceData(
                            $form->insurance_company_id,
                            $form->insurance_expire_date ? new \DateTime($form->insurance_expire_date) : null,
                            $form->insurance_policy_number,
                            $form->insurer
                        ),
                        $form->phones
                    );

                    return ['status' => 'success', 'data' => Yii::createObject('yii\rest\Serializer')->serialize($model)];
                } else {
                    return ['status' => 'error', 'errors' => $form->firstErrors];
                }
            }
        }
        return ['status' => 'success', 'message'=> "No data"];
    }

    /**
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CompanyCustomer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = CompanyCustomer::findOne([
            'id'         => $id,
            'company_id' => Yii::$app->user->identity->company_id,
        ]);
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }

    /**
     * @param $model
     */
    private function setCustomerSidebarOptions($model)
    {
        $this->view->params['sideNavView'] = 'tree';
        $this->view->params['sideNavOptions'] = [
            [
                'label' => $model->customer->name,
                'icon' => 'icon gender_icon sprite-customer_male',
                'url' => ['customer/view', 'id' => $model->id],
            ],
            [
                'label' => Yii::t('app', 'Info'),
                'icon' => 'icon sprite-customer_personal_data',
                'url' => ['customer/view', 'id' => $model->id],
            ],
            [
                'label' => Yii::t('app', 'Order History'),
                'icon' => 'icon sprite-customer_personal_data',
                'url' => ['customer/history', 'id' => $model->id],
            ],
        ];
    }

    /**
     * @param $id
     * @return Response
     */
    public function actionRestore($id)
    {
        $this->findModel($id);

        try {
            $this->companyCustomerService->restore($id);
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
     * @return Response
     */
    private function gotoNewDesign()
    {
        $new_design_link = Yii::$app->params['vue_host']."/customers";

        return $this->redirect($new_design_link);
    }

    /**
     * @param $id
     * @return string
     * @throws UnprocessableEntityHttpException
     */
    public function actionMerge($id)
    {
        $model = $this->findModel($id);

        $form = new MergeForm($id);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->companyCustomerService->merge($id, $form->customer_ids);
            return "Клиенты успешно объединены.";
        }

        throw new UnprocessableEntityHttpException();
    }
}
