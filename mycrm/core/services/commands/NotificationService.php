<?php

namespace core\services\commands;

use common\components\events\order\CheckoutOrderEvent;
use common\components\events\order\ResetOrderEvent;
use core\helpers\customer\RequestTemplateHelper;
use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerRequest;
use core\models\customer\CustomerRequestTemplate;
use core\models\customer\DelayedNotification;
use core\models\order\Order;
use core\repositories\company\CompanyRepository;
use core\repositories\customer\CompanyCustomerRepository;
use core\repositories\customer\CustomerRequestTemplateRepository;

class NotificationService
{
    private $customerRequestTemplateRepository;
    private $companyCustomerRepository;
    private $companyRepository;

    public function __construct(
        CustomerRequestTemplateRepository $customerRequestTemplateRepository,
        CompanyCustomerRepository $companyCustomerRepository,
        CompanyRepository $companyRepository
    ) {
        $this->companyRepository = $companyRepository;
        $this->companyCustomerRepository = $companyCustomerRepository;
        $this->customerRequestTemplateRepository = $customerRequestTemplateRepository;
    }

    /**
     * @return integer number of messages sent
     */
    public function notifyBirthDay()
    {
        $templates = $this->customerRequestTemplateRepository->findByType(CustomerRequestTemplate::TYPE_BIRTHDAY);

        return array_reduce($templates, function($sentMessagesCount, CustomerRequestTemplate $template) {

            $company = $this->companyRepository->find($template->company_id);
            $companyCustomers = $this->companyCustomerRepository->findAllByCompanyHavingBirthdayToday($template->company_id);

            // Skip company if not enough balance
            if ($company->getSmsLimit() < count($companyCustomers)) {
                return $sentMessagesCount;
            }

            foreach ($companyCustomers as $companyCustomer) {
                // Stop sending message if not enough balance
                if ( ! $company->hasEnoughBalance(\Yii::$app->params['sms_cost'])) {
                    break;
                }

                if (CustomerRequest::sendTemplateRequest($companyCustomer, CustomerRequestTemplate::TYPE_BIRTHDAY)) {
                    $sentMessagesCount++;
                }
            }

            return $sentMessagesCount;
        }, 0);
    }

    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function sendDelayedNotifications()
    {
        $messageCount = 0;

        /** @var DelayedNotification[] $delayedNotifications */
        $delayedNotifications = DelayedNotification::find()
            ->with('companyCustomer.customer')
            ->andWhere([
                'date'   => date("Y-m-d"),
                'status' => DelayedNotification::STATUS_NEW,
            ])->all();

        foreach ($delayedNotifications as $delayedNotification) {
            $data = [
                'CLIENT_NAME'  => $delayedNotification->companyCustomer->customer->name,
                'CLIENT_PHONE' => $delayedNotification->companyCustomer->customer->phone,
                'SERVICE_NAME' => $delayedNotification->divisionService->service_name
            ];

            if (CustomerRequest::sendTemplateRequest($delayedNotification->companyCustomer,
                CustomerRequestTemplate::TYPE_NOTIFY_HEALTH_EXAMINATION,
                $data)
            ) {
                $delayedNotification->updateAttributes([
                    'status'      => DelayedNotification::STATUS_EXECUTED,
                    'executed_at' => date("Y-m-d H:i:s")
                ]);

                $messageCount++;
            }
        }

        return $messageCount;
    }

    /**
     * Receives event with order data, for division services with specified notification delay creates
     * delayed notification
     * @param CheckoutOrderEvent $event
     */
    public static function addDelayedNotifications(CheckoutOrderEvent $event)
    {
        $order = $event->order;

        $templateEnabled = CustomerRequestTemplate::find()->where([
            'is_enabled' => true,
            'key'        => CustomerRequestTemplate::TYPE_NOTIFY_HEALTH_EXAMINATION,
            'company_id' => $order->companyCustomer->company_id
        ])->exists();

        if ($templateEnabled) {
            foreach ($order->orderServices as $key => $orderService) {
                if ($orderService->divisionService->notification_delay) {

                    $delay = $orderService->divisionService->getDelay();
                    $notificationDate = (new \DateTime($order->datetime))->modify("+{$delay}");

                    // cancel notifications whose date is earlier than new notification
                    DelayedNotification::updateAll(['status' => DelayedNotification::STATUS_CANCELED], [
                        "AND",
                        ['status' => DelayedNotification::STATUS_NEW],
                        ['company_customer_id' => $order->company_customer_id],
                        ['division_service_id' => $orderService->division_service_id],
                        ["<", 'date', $notificationDate->format("Y-m-d")]
                    ]);

                    // check if there is already notification on the same date
                    /** @var DelayedNotification $delayedNotification */
                    $delayedNotification = DelayedNotification::find()->where([
                        'status'              => [
                            DelayedNotification::STATUS_NEW,
                            DelayedNotification::STATUS_CANCELED
                        ],
                        'company_customer_id' => $order->company_customer_id,
                        'date'                => $notificationDate->format("Y-m-d"),
                        'division_service_id' => $orderService->division_service_id,
                    ])->one();

                    if (!$delayedNotification) {
                        $delayedNotification = new DelayedNotification([
                            'company_customer_id' => $order->company_customer_id,
                            'date'                => $notificationDate->format("Y-m-d"),
                            'division_service_id' => $orderService->division_service_id,
                            'interval'            => $delay
                        ]);
                    } else {
                        $delayedNotification->enable();
                    }
                    $delayedNotification->save(false);
                }
            }
        }
    }

    /**
     * Remove delayed notifications. Runs when order was reset
     * @param ResetOrderEvent $event
     */
    public static function removeDelayedNotifications(ResetOrderEvent $event)
    {
        $order = $event->order;
        foreach ($order->orderServices as $key => $orderService) {

            // check if there is another finished order with the same service
            $orderWithSameService = Order::find()
                ->finished()
                ->joinWith('orderServices', false)
                ->andWhere([
                    '{{%order_services}}.division_service_id' => $orderService->division_service_id,
                    '{{%order_services}}.deleted_time'        => null,
                ])->startFrom(new \DateTime($order->datetime))
                ->to(new \DateTime($order->datetime))
                ->exists();

            if (!$orderWithSameService) {
                DelayedNotification::updateAll(['status' => DelayedNotification::STATUS_CANCELED], [
                    "AND",
                    ['company_customer_id' => $order->company_customer_id],
                    ['division_service_id' => $orderService->division_service_id],
                    ['status' => DelayedNotification::STATUS_NEW],
                    '[[date]] = DATE(:date) + [[interval]]::INTERVAL'
                ], [':date' => $order->datetime]);
            }
        }
    }

    /**
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function notifySinceLastVisit()
    {
        $messagesCount = 0;

        /** @var \core\models\company\Company[] $companies */
        $companies = \core\models\company\Company::find()
            ->enabled()
            ->joinWith('customerRequestTemplates')
            ->andWhere([
                CustomerRequestTemplate::tableName() . '.is_enabled' => true,
                CustomerRequestTemplate::tableName() . '.key'        => CustomerRequestTemplate::TYPE_NOTIFY_CUSTOMER_SINCE_LAST_VISIT,
            ])->all();

        foreach ($companies as $company) {
            /** @var CustomerRequestTemplate $template */
            $template = $company->getCustomerRequestTemplates()
                ->andWhere(['key' => CustomerRequestTemplate::TYPE_NOTIFY_CUSTOMER_SINCE_LAST_VISIT])
                ->one();

            $currentDate = (new \DateTimeImmutable());
            $interval = "-{$template->quantity} " . RequestTemplateHelper::getQuantityType($template->quantity_type);
            $lastVisitDate = $currentDate->modify($interval);

            $companyCustomersIds = Order::find()
                ->select(['{{%orders}}.company_customer_id'])
                ->company(false, $company->id)
                ->finished()
                ->groupBy("{{%orders}}.company_customer_id")
                ->having(['date(max(datetime))' => $lastVisitDate->format("Y-m-d")])
                ->column();

            /** @var CompanyCustomer[] $companyCustomers */
            $companyCustomers = CompanyCustomer::find()->with(['customer'])->where(['id' => $companyCustomersIds])->all();

            foreach ($companyCustomers as $companyCustomer) {
                $data = [
                    'CLIENT_NAME'  => $companyCustomer->customer->name,
                    'CLIENT_PHONE' => $companyCustomer->customer->phone,
                ];
                if (CustomerRequest::sendTemplateRequest($companyCustomer, $template->key, $data)) {
                    $messagesCount++;
                }
            }
        }

        return $messagesCount;
    }
}
