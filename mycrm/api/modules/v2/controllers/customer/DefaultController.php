<?php

namespace api\modules\v2\controllers\customer;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\customer\CustomerSearch;
use api\modules\v2\search\customer\LostCustomerSearch;
use common\components\parsers\CustomerParser;
use core\forms\customer\CompanyCustomerAddCategoriesForm;
use core\forms\customer\CompanyCustomerCreateForm;
use core\forms\customer\CompanyCustomerMultipleForm;
use core\forms\customer\CompanyCustomerPayDebtForm;
use core\forms\customer\CompanyCustomerSendRequestForm;
use core\forms\customer\CompanyCustomerUpdateForm;
use core\forms\customer\MergeForm;
use core\forms\ImportForm;
use core\models\customer\CompanyCustomer;
use core\models\customer\CompanyCustomerHistory;
use core\models\customer\CustomerHistory;
use core\models\HistoryEntity;
use core\services\customer\CompanyCustomerService;
use core\services\dto\CustomerData;
use core\services\dto\CustomerInsuranceData;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class DefaultController extends BaseController
{
    public $modelClass = 'core\models\customer\CompanyCustomer';

    private $companyCustomerService;

    public function __construct(
        $id,
        $module,
        CompanyCustomerService $companyCustomerService,
        $config = []
    ) {
        $this->companyCustomerService = $companyCustomerService;
        parent::__construct($id, $module, $config = []);
    }

    /**
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'actions' => [
                        'index',
                        'update',
                        'create',
                        'view',
                        'pay-debt',
                        'options',
                        'export',
                        'import',
                        'archive',
                        'delete-multiple',
                        'send-request-multiple',
                        'add-categories-multiple',
                        'lost',
                        'history',
                        'merge',
                        'upload-avatar'
                    ],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete'], $actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        $searchModel = new CustomerSearch();
        $searchModel->is_active = true;

        $dataProvider =  $searchModel->search(\Yii::$app->request->queryParams);

        return $dataProvider;
    }

    public function actionArchive()
    {
        $searchModel = new CustomerSearch();
        $searchModel->is_active = false;
        return $searchModel->search(\Yii::$app->request->queryParams);
    }

    /**
     * @param string          $action
     * @param CompanyCustomer $model
     * @param array           $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['merge', 'view', 'update'])) {
            if ($model->company_id !== \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @return CompanyCustomer|CompanyCustomerCreateForm
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new CompanyCustomerCreateForm();
        $form->load(Yii::$app->request->bodyParams, '');

        if ( ! $form->validate()) {
            return $form;
        }

        $imageFile = UploadedFile::getInstance($form, 'imageFile');

        return $this->companyCustomerService->createCustomer(
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
            Yii::$app->user->identity->company_id,
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
    }

    /**
     * @param integer $id
     *
     * @return CompanyCustomer|CompanyCustomerUpdateForm
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $companyCustomer = $this->findModel($id);

        $canUpdateCompanyCustomer = Yii::$app->user->can(
            "companyCustomerUpdate",
            ["model" => $companyCustomer]
        );
        if ( ! $canUpdateCompanyCustomer) {
            throw new ForbiddenHttpException('Not allowed');
        }

        $form = new CompanyCustomerUpdateForm($companyCustomer);
        $form->load(Yii::$app->request->bodyParams, '');

        if ( ! $form->validate()) {
            return $form;
        }

        $imageFile = UploadedFile::getInstance($form, 'imageFile');

        return $this->companyCustomerService->updateProfile(
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
    }

    /**
     * @param $id
     */
    public function actionUploadAvatar($id) {
        $companyCustomer = $this->findModel($id);
        $image = UploadedFile::getInstanceByName('image');

        return $this->companyCustomerService->changeAvatar($companyCustomer->id, $image);
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionPayDebt($id)
    {
        $model = $this->findModel($id);

        $form = new CompanyCustomerPayDebtForm($model);
        $form->load(Yii::$app->request->bodyParams, '');

        if ( ! $form->validate()) {
            return $form;
        }

        return $this->companyCustomerService->payDebt(
            $model->id,
            $form->amount,
            Yii::$app->user->id,
            new \DateTime()
        );

    }

    public function actionExport()
    {
        $searchModel = new CustomerSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        $this->companyCustomerService->export($dataProvider);
    }

    /**
     * @return array|ImportForm
     * @throws \Exception
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \yii\db\Exception
     */
    public function actionImport()
    {
        $model = new ImportForm();

        /**
         * If used with the following code
         * $model->excelFile = UploadedFile::getInstance($model, 'excelFile');
         * POST request should contain "ImportForm[excelFile]" field, otherwise just "excelFile"
         */
        $model->excelFile = UploadedFile::getInstanceByName('excelFile');

        Yii::$app->session->set('progress', 0);

        if ( ! $model->validate()) {
            return $model;
        }

        // TODO import and return format should be checked
        $savedCount = CustomerParser::parse($model, Yii::$app->user->identity->company);
        return [
            'message' => Yii::t('app', '{number} clients were uploaded, please go through the link {link}',
                [
                    'number' => $savedCount,
                    'link' => Html::a('предпросмотра', ['temp/index'])
                ])
        ];
    }

    /**
     * @return array|CompanyCustomerMultipleForm
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function actionDeleteMultiple()
    {
        $form = new CompanyCustomerMultipleForm();
        $form->load(Yii::$app->request->bodyParams, '');

        if ( ! $form->validate()) {
            return $form;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            /** @var CompanyCustomer[] $companyCustomers */
            $companyCustomers = CompanyCustomer::find()
                ->company()
                ->andWhere(['IN', 'id', $form->ids])
                ->all();

            foreach ($companyCustomers as $companyCustomer) {
                if (!Yii::$app->user->can("companyCustomerDelete", ["model" => $companyCustomer])) {
                    throw new ForbiddenHttpException('Not allowed');
                }

                $companyCustomer->is_active = false;
                if (!$companyCustomer->save()) {
                    throw new \Exception("Error while updating");
                }
            }

            $transaction->commit();
            return [
                'message' => 'success',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Send SMS with the given message to multiple CompanyCustomers
     */
    public function actionSendRequestMultiple()
    {
        $form = new CompanyCustomerSendRequestForm();
        $form->load(Yii::$app->request->bodyParams, '');

        if ( ! $form->validate()) {
            return $form;
        }

        /** @var CompanyCustomer[] $companyCustomers */
        $companyCustomers = CompanyCustomer::find()
            ->company()
            ->andWhere(['IN', 'id', $form->ids])
            ->all();

        $company = Yii::$app->user->identity->company;
        $message = $form->message;

        if ($this->companyCustomerService->sendRequest($companyCustomers, $company, $message)) {
            return [
                'status' => 'success',
                'message' => 'SMS успешно отправлены',
            ];
        }
    }

    /**
     * Add multiple CompanyCustomers to given multiple categories
     */
    public function actionAddCategoriesMultiple()
    {
        $form = new CompanyCustomerAddCategoriesForm();
        $form->load(Yii::$app->request->bodyParams, '');

        if ( ! $form->validate()) {
            return $form;
        }

        /** @var CompanyCustomer[] $companyCustomers */
        $companyCustomers = CompanyCustomer::find()
            ->company()
            ->andWhere(['IN', 'id', $form->ids])
            ->indexBy('id')
            ->all();

        foreach ($companyCustomers as $companyCustomer) {
            $this->companyCustomerService->addCategories($companyCustomer->id, $form->category_ids);
        }

        return [
            'status' => 'success',
        ];
    }


    /**
     * @return ActiveDataProvider
     */
    public function actionLost(){
        $searchModel = new LostCustomerSearch();
        return $searchModel->search(Yii::$app->request->get());
    }


    /**
     * Returns customer history view
     * P.S функция группирует CustomerHistory И CompanyCustomerHistory по времени создания. Есть нюанс, что при запуске
     * теста создание и редактирование объектов происходит мгновенно(одновременно), и функция возвратит только один
     * HistoryEntity (так как они сгруппируются по времени создания).
     *
     * @param integer $id
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionHistory($id)
    {
        $companyCustomer = $this->findModel($id);
        /** @var CompanyCustomerHistory[] $companyCustomerHistories */
        $companyCustomerHistories = $companyCustomer->getHistories()
            ->orderBy("created_time ASC")
            ->all();

        /** @var CustomerHistory[] $customerHistories */
        $customerHistories = $companyCustomer->customer->getHistories()
            ->orderBy("created_time ASC")
            ->all();

        /** @var HistoryEntity[] $result */
        $result = [];
        foreach ($companyCustomerHistories as $companyCustomerHistory) {
            $result[$companyCustomerHistory->created_time] = $companyCustomerHistory;
        }

        foreach ($customerHistories as $customerHistory) {
            if(array_key_exists($customerHistory->created_time, $result)) {
                $result[$customerHistory->created_time]->log = $this->array_merge_recursive_distinct(
                    $result[$customerHistory->created_time]->log, $customerHistory->log
                );
            } else {
                $result[$customerHistory->created_time] = $customerHistory;
            }
        }

        return array_values($result);
    }

    /**
     * @param $id
     * @return MergeForm|CompanyCustomer
     */
    public function actionMerge($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($this->action->id, $model);

        $form = new MergeForm($id);
        $form->load(Yii::$app->request->getBodyParams());

        if ($form->validate()) {
            return $this->companyCustomerService->merge($id, $form->customer_ids);
        }

        return $form;
    }

    /**
     * TODO ugly coding
     *
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param mixed $array2
     *
     * @return array
     * @author daniel@danielsmedegaardbuus.dk
     */
    private function array_merge_recursive_distinct(array $array1, array $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset ($merged [$key])
                && is_array($merged [$key])
            ) {
                $merged [$key]
                    = $this->array_merge_recursive_distinct($merged [$key],
                    $value);
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return CompanyCustomer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel($id)
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
}
