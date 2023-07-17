<?php

namespace api\modules\v2\controllers\common;

use api\modules\v2\OptionsTrait;
use core\forms\order\OrderCreateForm;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\finance\CompanyCash;
use core\services\dto\CustomerData;
use core\services\order\dto\OrderData;
use core\services\order\dto\OrderServiceData;
use core\services\order\OrderModelService;
use yii\base\Module;
use yii\rest\ActiveController;

class OrderController extends ActiveController
{
    use OptionsTrait;

    public $modelClass = false;

    private $_service;

    /**
     * OrderController constructor.
     * @param string $id
     * @param Module $module
     * @param OrderModelService $service
     * @param array $config
     */
    public function __construct($id, Module $module, OrderModelService $service, array $config = [])
    {
        $this->_service = $service;
        parent::__construct($id, $module, $config);
    }

    /**
     * @param \yii\base\Action $action
     *
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->getOptionsHeaders();

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete'], $actions['index'], $actions['view']);
        return $actions;
    }

    /**
     * @return OrderCreateForm|\core\models\order\Order
     * @throws \Exception
     */
    public function actionCreate()
    {
        $form = new OrderCreateForm();
        $form->setScenario(OrderCreateForm::SCENARIO_API);
        $form->load(\Yii::$app->request->bodyParams, "");

        if (!$form->validate()) {
            return $form;
        }

        $servicesData = $this->getServices($form->service_id);

        $division = Division::findOne($form->division_id);
        $company_cash = $division->company->getCompanyCashes()
            ->where(['status' => CompanyCash::STATUS_ENABLED])->one();

        $this->_service->create(
            new OrderData(
                new \DateTime($form->datetime),
                $form->division_id,
                $form->staff_id,
                null,
                false,
                null,
                $company_cash->id,
                null,
                $division->company_id,
                null,
                null
            ),
            $servicesData,
            [],
            [],
            [],
            new CustomerData(
                null,
                $form->customer_name,
                null,
                null,
                $form->customer_phone,
                null
            )
        );

        return \Yii::$app->response->setStatusCode(201);
    }

    /**
     * @param int $service_id
     * @return array
     */
    private function getServices(int $service_id)
    {
        $service = DivisionService::findOne($service_id);

        return [new OrderServiceData($service_id, $service->price, $service->average_time, 0, 1)];
    }
}