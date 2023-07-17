<?php

namespace api\tests\user;

use FunctionalTester;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\order\Order;
use core\models\order\OrderDocument;
use core\models\order\OrderDocumentTemplate;
use core\models\Staff;
use core\models\StaffDivisionMap;

class DocumentCest
{
    private $responseFormat = [
        'date' => 'string:datetime',
        'link' => 'string',
        'templateName' => 'string',
        'userName' => 'string',
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('order/1/document');
        $I->seeResponseCodeIs(401);

        $I->login();

        $order = $I->getFactory()->create(Order::class);

        $I->getFactory()->seed(10, OrderDocument::class, [
            'order_id' => $order->id
        ]);

        $I->sendGET("order/{$order->id}/document");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->sendGET('order/1/document/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id
        ]);
        $order = $I->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id
        ]);
        $model = $I->getFactory()->create(OrderDocument::class, [
            'order_id' => $order->id
        ]);

        $I->sendGET("order/{$order->id}/document/{$model->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
