<?php

namespace core\jobs\customer;

use core\services\customer\LoyaltyManager;
use Yii;
use yii\base\BaseObject;

class RewardCustomerJob extends BaseObject implements \yii\queue\JobInterface
{
    /** @var int */
    public $customerId;

    /**@var LoyaltyManager */
    private $service;

    /**
     * RewardCustomerJob constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->service = Yii::$app->loyaltyManager;

        parent::__construct($config);
    }

    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        $this->service->reward($this->customerId);
    }
}