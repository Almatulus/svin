<?php

namespace core\services\customer;

use core\forms\customer\SmscCallbackForm;
use core\models\customer\CustomerRequest;
use core\repositories\customer\CustomerRequestRepository;
use core\services\TransactionManager;
use Yii;

class CustomerRequestService
{
    private $transactionManager;
    /**
     * @var CustomerRequestRepository
     */
    private $customerRequests;

    public function __construct(
        CustomerRequestRepository $customerRequests,
        TransactionManager $transactionManager
    ) {
        $this->customerRequests    = $customerRequests;
        $this->transactionManager = $transactionManager;
    }

    public function sendSms($id)
    {
        $customerRequest = $this->customerRequests->find($id);

        if( $customerRequest ){
            $message = str_replace("'", '', $customerRequest->code);
            $result = json_decode(Yii::$app->sms->send($customerRequest->receiver_phone, $message), true);

            $customerRequest->updateSmscInfo(
                $result['id'] ?? null,
                $result['cost'] ?? null,
                $result['cnt'] ?? null,
                $result['error_code'] ?? null,
                null
            );

            $this->transactionManager->execute(function () use ($customerRequest) {
                $this->customerRequests->edit($customerRequest);
            });
        }
    }

    public function getTotalSmsCount($models)
    {
        return array_reduce($models, function ($count, CustomerRequest $model){
            $count += $model->getSmsCount();
            return $count;
        }, 0);
    }

    public function getTotalPrice($models)
    {
        return array_reduce($models, function ($totalPrice, CustomerRequest $model){
            $totalPrice += $model->getPrice();
            return $totalPrice;
        }, 0);
    }

    public function processCallback(SmscCallbackForm $form)
    {
        $customerRequest = $this->customerRequests->findBySmscId($form->id);
        $customerRequest->updateSmscInfo($form->id, $form->cost, $form->cnt, $form->err, $form->status);

        $this->customerRequests->edit($customerRequest);
    }
}