<?php

namespace api\tests\statistic;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\order\Order;
use FunctionalTester;

class CustomerCest
{
    private $responseFormat = [
        'id'           => 'integer',
        'fullName'     => 'string',
        'phone'        => 'string',
        'averageCheck' => 'integer|string',
        'ordersCount'  => 'integer',
        'revenue'      => 'integer|string'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function index(FunctionalTester $I)
    {
        $I->wantToTest("Statistic customer index");

        $I->sendGET('statistic/customer');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('statistic/customer');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([]);

        $companyCustomers = $I->getFactory()->seed(2, CompanyCustomer::class, [
            'company_id' => $user->company_id
        ]);
        foreach ($companyCustomers as $companyCustomer) {
            $I->getFactory()->create(Order::class, [
                'company_customer_id' => $companyCustomer->id,
                'status'              => OrderConstants::STATUS_FINISHED
            ]);
        }

        $I->sendGET('statistic/customer');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    // tests
    public function top(FunctionalTester $I)
    {
        $I->wantToTest("Statistic customer top");

        $I->sendGET('statistic/customer/top');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('statistic/customer/top');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'maxVisits'  => 'null',
            'maxRevenue' => 'null',
            'maxDebt'    => 'null',
        ]);

        $companyCustomers = $I->getFactory()->seed(2, CompanyCustomer::class, [
            'company_id' => $user->company_id,
            'balance'    => -1000
        ]);
        foreach ($companyCustomers as $companyCustomer) {
            $I->getFactory()->create(Order::class, [
                'company_customer_id' => $companyCustomer->id,
                'status'              => OrderConstants::STATUS_FINISHED
            ]);
        }
        $I->sendGET('statistic/customer/top');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'maxVisits'  => $this->responseFormat,
            'maxRevenue' => $this->responseFormat,
            'maxDebt'    => $this->responseFormat,
        ]);
    }
}
