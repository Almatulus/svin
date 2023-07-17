<?php
namespace core\jobs\customer;
use core\services\customer\CustomerRequestService;
use Yii;
use yii\base\BaseObject;

class SendCustomerRequestSmsJob extends BaseObject implements \yii\queue\JobInterface
{
    public $requestId;
    /**@var CustomerRequestService */
    private $service;

    public function __construct(array $config = [])
    {
        $this->service = Yii::$container->get('core\services\customer\CustomerRequestService');
        parent::__construct($config);
    }

    public function execute($queue)
    {
        $this->service->sendSms($this->requestId);
    }
}