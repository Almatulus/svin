<?php

namespace api\modules\v2\controllers\company;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\company\CompanyPaymentLogSearch;
use core\models\CompanyPaymentLog;
use core\services\company\PaymentService;
use Yii;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

class PaymentController extends BaseController
{
    public $modelClass = 'core\models\CompanyPaymentLog';

    /** @var PaymentService */
    private $service;

    /**
     * PaymentController constructor.
     * @param string $id
     * @param Module $module
     * @param PaymentService $service
     * @param array $config
     */
    public function __construct($id, Module $module, PaymentService $service, array $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
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
                        'export',
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
        $searchModel             = new CompanyPaymentLogSearch();
        $searchModel->company_id = Yii::$app->user->identity->company_id;

        return $searchModel->search(Yii::$app->request->queryParams);
    }

    /**
     * @param string            $action
     * @param CompanyPaymentLog $model
     * @param array             $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view'])) {
            if ($model->company_id !== \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * Returns excel file
     */
    public function actionExport()
    {
        $dataProvider = $this->prepareDataProvider();

        $this->service->export($dataProvider->getModels());
    }
}
