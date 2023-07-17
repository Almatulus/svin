<?php

namespace api\modules\v2\controllers\order;

use api\modules\v2\controllers\BaseController;
use api\modules\v2\search\order\OrderSearch;
use core\forms\order\OrderMoveForm;
use core\forms\order\PendingOrderForm;
use core\helpers\order\OrderConstants;
use core\models\order\Order;
use core\services\dto\CustomerData;
use core\services\order\OrderModelService;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class PendingController extends BaseController
{
    public $modelClass = 'core\models\order\Order';
    private $orderService;

    public function __construct(
        $id,
        $module,
        OrderModelService $orderService,
        $config = []
    ) {
        $this->orderService = $orderService;
        parent::__construct($id, $module, $config = []);
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
        $searchModel         = new OrderSearch();
        $searchModel->status = OrderConstants::STATUS_WAITING;

        return $searchModel->search(Yii::$app->request->queryParams, false);
    }

    /**
     * @return Order|PendingOrderForm
     */
    public function actionCreate()
    {
        $form = new PendingOrderForm();
        $form->load(Yii::$app->request->bodyParams, '');

        if ( ! $form->validate()) {
            return $form;
        }

        return $this->orderService->addPending(
            new CustomerData(
                $form->company_customer_id,
                $form->customer_name,
                null,
                null,
                $form->customer_phone,
                null
            ),
            $form->date,
            $form->note,
            $form->staff_id,
            $form->division_id
        );
    }

    /**
     * @param $id
     *
     * @return Order|PendingOrderForm
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        $form = new PendingOrderForm();
        $form->load(Yii::$app->request->bodyParams, '');

        if ( ! $form->validate()) {
            return $form;
        }

        return $this->orderService->editPending(
            $id,
            new CustomerData(
                $form->company_customer_id,
                $form->customer_name,
                null,
                null,
                $form->customer_phone,
                null
            ),
            $form->date,
            $form->note,
            $form->staff_id,
            $form->division_id
        );
    }

    /**
     * @param $id
     *
     * @return OrderMoveForm|Order
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionEnable($id)
    {
        $model = $this->findModel($id);

        if ( ! Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new ForbiddenHttpException('Not Allowed to update object');
        }

        if ($model->status !== OrderConstants::STATUS_WAITING) {
            throw new ForbiddenHttpException('Not Allowed to enable');
        }

        $form = new OrderMoveForm($model);
        $form->load(Yii::$app->request->bodyParams, '');
        $form->staff = $model->staff_id;

        if ( ! $form->validate()) {
            return $form;
        }

        $this->orderService->move($model->id, $model->staff_id, $form->start);

        return $this->orderService->enable($model->id);
    }

    /**
     * @param $id
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can("orderUpdate", ['model' => $model])) {
            throw new ForbiddenHttpException('Not Allowed to update object');
        }

        if ($model->status !== OrderConstants::STATUS_WAITING) {
            throw new ForbiddenHttpException('Not Allowed to enable');
        }

        $this->orderService->deletePending($id);

        Yii::$app->response->setStatusCode(204);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /* @var Order $model */
        $model = Order::find()
            ->permitted()
            ->andWhere([
                '{{%orders}}.id'     => $id,
                '{{%orders}}.status' => OrderConstants::STATUS_WAITING,
            ])
            ->one();
        if ($model === null) {
            throw new NotFoundHttpException(
                'The requested page does not exist.'
            );
        }

        return $model;
    }

}
