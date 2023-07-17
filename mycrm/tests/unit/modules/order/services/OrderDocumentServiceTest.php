<?php

namespace modules\order\services;

use Codeception\Stub;
use core\models\order\Order;
use core\models\order\OrderDocument;
use core\models\order\OrderDocumentTemplate;
use core\repositories\BaseRepository;
use core\repositories\order\OrderRepository;
use core\services\order\OrderDocumentService;
use core\services\TransactionManager;

class OrderDocumentServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var OrderDocumentService */
    protected $service;

    public function testAdd()
    {
        $order = $this->tester->getFactory()->create(Order::class);

        $template = $this->tester->getFactory()->create(OrderDocumentTemplate::class);

        $orderDocument = $this->service->add($order->id, $template->id, $order->created_user_id);

        verify($orderDocument)->isInstanceOf(OrderDocument::class);
        verify($orderDocument->id)->notNull();

        $this->tester->canSeeRecord(OrderDocument::class, [
            'order_id'    => $order->id,
            'template_id' => $template->id
        ]);
    }

    protected function _before()
    {
        $this->service = Stub::construct(OrderDocumentService::class, [
            'baseRepository'     => new BaseRepository(),
            'orderRepository'    => new OrderRepository(),
            'transactionManager' => new TransactionManager()
        ], [
            'generateDocument' => function () {
                return $this->tester->getFaker()->text;
            }
        ]);
    }

    // tests

    protected function _after()
    {
    }
}