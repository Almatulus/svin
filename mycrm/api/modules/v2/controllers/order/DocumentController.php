<?php

namespace api\modules\v2\controllers\order;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\order\OrderDocumentSearch;
use core\forms\order\OrderDocumentForm;
use core\models\order\OrderDocument;
use core\services\order\OrderDocumentService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

class DocumentController extends BaseController
{
    public $modelClass = 'core\models\order\OrderDocument';

    private $service;

    /**
     * DocumentController constructor.
     *
     * @param string               $id
     * @param \yii\base\Module     $module
     * @param OrderDocumentService $service
     * @param array                $config
     */
    public function __construct(
        $id,
        $module,
        OrderDocumentService $service,
        $config = []
    ) {
        $this->service = $service;
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
                    'actions' => ['index', 'create', 'view', 'options'],
                    'allow'   => true,
                    'roles'   => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions(): array
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
     * @return \yii\data\ActiveDataProvider
     */
    public function prepareDataProvider(): ActiveDataProvider
    {
        $searchModel = new OrderDocumentSearch();

        return $searchModel->search(Yii::$app->request->bodyParams);
    }

    /**
     * @param string        $action
     * @param OrderDocument $model
     * @param array         $params
     *
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['view'])) {
            if ($model->order->companyCustomer->company_id !== \Yii::$app->user->identity->company_id) {
                throw new ForbiddenHttpException('You are not allowed to act on this object');
            }
        }
    }

    /**
     * @param $order_id
     *
     * @return array|string
     * @throws \Exception
     */
    public function actionCreate($order_id)
    {
        $form = new OrderDocumentForm();
        $form->load(Yii::$app->request->bodyParams);
        $form->order_id = $order_id;

        if ($form->validate()) {
            return $this->service->add(
                $form->order_id,
                $form->template_id,
                Yii::$app->user->id
            );
        }

        return $form;
    }
}
