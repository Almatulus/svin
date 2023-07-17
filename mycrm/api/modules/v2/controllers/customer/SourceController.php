<?php

namespace api\modules\v2\controllers\customer;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\customer\CompanyCustomerSourceSearch;
use core\models\customer\CustomerSource;
use core\services\customer\CustomerSourceService;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class SourceController extends BaseController
{
    public $modelClass = 'core\models\customer\CustomerSource';

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
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'actions' => [
                        'index',
                        'view',
                        'create',
                        'update',
                        'move',
                        'options',
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
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = [
            $this,
            'prepareDataProvider',
        ];

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataProvider()
    {
        $searchModel = new CompanyCustomerSourceSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        return $dataProvider;
    }

    /**
     * @param string         $action
     * @param CustomerSource $model
     * @param array          $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view', 'update'])) {
            if ($model->company_id !== \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @return CustomerSource
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionCreate()
    {
        $model = new CustomerSource();
        $model->load(Yii::$app->request->bodyParams, '');

        if ( ! $model->validate()) {
            $errors = $model->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $model = $this->service->create($model->name, Yii::$app->user->identity->company_id);

        Yii::$app->getResponse()->setStatusCode(201);
        $model->refresh();

        return $model;
    }

    /**
     * @return CustomerSource
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        $model = CustomerSource::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Model not exists');
        }

        $model->load(Yii::$app->request->bodyParams, '');

        if ( ! $model->validate()) {
            $errors = $model->getErrors();
            throw new BadRequestHttpException(reset($errors)[0]);
        }

        $this->service->update($id, $model->name);

        return $model;
    }

    /**
     * @param $id
     * @param $destination_id
     * @return array
     */
    public function actionMove($id, $destination_id){
        $updated = $this->service->moveCustomers(
            $id,
            $destination_id,
            Yii::$app->user->identity->company_id
        );

        return ['total_moved' => $updated];
    }
}
