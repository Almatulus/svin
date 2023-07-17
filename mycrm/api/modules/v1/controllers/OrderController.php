<?php

namespace api\modules\v1\controllers;

use api\modules\v1\components\ApiController;
use core\forms\order\OrderCreateForm;
use core\helpers\order\OrderConstants;
use core\models\customer\Customer;
use core\services\customer\CompanyCustomerService;
use core\services\dto\CustomerData;
use core\services\order\dto\OrderData;
use core\services\order\dto\OrderServiceData;
use core\services\order\OrderApiService;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;

class OrderController extends ApiController
{
    private $orderService;
    private $companyCustomerService;

    public function __construct($id,
                                $module,
                                OrderApiService $orderService,
                                CompanyCustomerService $companyCustomerService,
                                $config = []
    )
    {
        $this->orderService = $orderService;
        $this->companyCustomerService = $companyCustomerService;
        parent::__construct($id, $module, $config = []);
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => QueryParamAuth::className(),
                'except' => ['create-unauthorized'],
                'tokenParam' => 'token',
            ]
        ]);
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\ExitException
     * @throws \Exception
     */
    public function actionCreateUnauthorized()
    {
        if (\Yii::$app->getRequest()->getMethod() === 'OPTIONS') {
            \Yii::$app->response->headers->set("Access-Control-Allow-Origin", "*");
            \Yii::$app->response->headers->set("X-Content-Type-Options", "nosniff");
            \Yii::$app->response->headers->set("Access-Control-Allow-Headers", "X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers");
            \Yii::$app->end();
        }

        $form = new OrderCreateForm();
        $form->ignoreNameWarning = true;
        $date = \Yii::$app->request->getBodyParam('date', null);
        $time = \Yii::$app->request->getBodyParam('time', null);
        $datetime = new \DateTime($date . " " . $time);
        $form->datetime = $datetime->format("Y-m-d H:i");;
        $form->division_id = \Yii::$app->request->getBodyParam('division', null);
        $form->customer_name = \Yii::$app->request->getBodyParam('name', null);
        $form->customer_phone = \Yii::$app->request->getBodyParam('phone', null);
        $form->hours_before = \Yii::$app->request->getBodyParam('hours_before', 0);
        $form->staff_id = \Yii::$app->request->getBodyParam('staff', null);
        $form->note = \Yii::$app->request->getBodyParam('comments', null);
        $form->services[0] = [
            'division_service_id' => \Yii::$app->request->getBodyParam('service', null),
            'quantity' => 1,
            'duration' => 0,
            'discount' => 0,
            'price' => 0
        ];

        /* @TODO Fix setting company cash automatically */
        $division = \core\models\division\Division::findOne($form->division_id);
        $company_cash = $division->company->getCompanyCashes()->where(['status' => \core\models\finance\CompanyCash::STATUS_ENABLED])->one();
        if ($company_cash) {
            $form->company_cash_id = $company_cash->id;
        }

        if (!$form->validate()) {
            throw new BadRequestHttpException('Bad request ' . Json::encode($form->errors));
        }

        $companyCustomer = $this->companyCustomerService->findByPhone($form->customer_phone, $division->company_id);
        if ($companyCustomer) {
            $customer = $companyCustomer->customer;
            $form->company_customer_id = $companyCustomer->id;
            $form->customer_name = $form->customer_name ?: $customer->name;
            $form->customer_surname = $customer->lastname;
            $form->customer_patronymic = $customer->patronymic;
        }

        $orderServices = [
            new OrderServiceData(
                intval($form->services[0]['division_service_id']),
                null,
                null,
                null,
                intval($form->services[0]['quantity'])
            )
        ];
        $this->orderService->create(
            new OrderData(
                new \DateTime($form->datetime),
                $form->division_id,
                $form->staff_id,
                $form->note,
                $form->hours_before,
                $form->color,
                $form->company_cash_id,
                null,
                null,
                null,
                null
            ),
            $orderServices,
            new CustomerData(
                $form->company_customer_id,
                $form->customer_name,
                $form->customer_surname,
                $form->customer_patronymic,
                $form->customer_phone,
                null
            )
        );

        return ['status' => 200, 'message' => 'ordered'];
    }

    public function actionList()
    {
        $customer = \Yii::$app->user->identity;
        /** @var Customer $customer */
        $company_customers = $customer->companyCustomers;

        $result = [];
        foreach ($company_customers as $company_customer) {
            $orders = $company_customer->orders;
            foreach ($orders as $order) {
                $division_images_model = $order->division->divisionImages;
                $image_logo = \Yii::$app->params['api_host'] . $order->division->company->logo->getPath();
                foreach ($division_images_model as $division_image) {
                    $image_logo = \Yii::$app->params['api_host'] . $division_image->image->getPath();
                    break;
                }
                $data = [
                    'id' => $order->id,
                    'status' => $order->status,
                    'status_name' => \Yii::t('app', OrderConstants::getStatuses()[$order->status]),
                    'service_id' => $order->divisionServices[0]->id,
                    'service_name' => $order->divisionServices[0]->service_name,
                    'division' => $order->division->getInformation(),
                    'division_id' => $order->division_id,
                    'division_name' => $order->division->name,
                    'division_address' => $order->division->address,
                    'staff_id' => $order->staff_id,
                    'price' => $order->price,
                    'source_id' => $order->type,
                    'source' => \Yii::t('app', OrderConstants::getTypes()[$order->type]),
                    'created_time' => $order->created_time,
                    'datetime' => $order->datetime,
                    'note' => $order->note,
                    'image' => $image_logo,
                ];

                if (in_array($order->status, [OrderConstants::STATUS_DISABLED, OrderConstants::STATUS_FINISHED])) {
                    $result['passed'][] = $data;
                } else {
                    $result['soon'][] = $data;
                }
            }
        }

        return $result;
    }
}
