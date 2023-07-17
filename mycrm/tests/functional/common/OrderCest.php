<?php

namespace common;


use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\order\Order;
use core\models\Staff;
use FunctionalTester;

class OrderCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function create(FunctionalTester $I)
    {
        $staff = $I->getFactory()->create(Staff::class);
        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $companyCustomer->company_id]);
        $service = $I->getFactory()->create(DivisionService::class);
        $datetime = new \DateTime();

        $I->sendPOST('public/order', [
            'customer_name'  => $companyCustomer->customer->name,
            'customer_phone' => $companyCustomer->customer->phone,
            'datetime'       => $datetime->format("Y-m-d H:i"),
            'division_id'    => $division->id,
            'service_id'     => $service->id,
            'staff_id'       => $staff->id
        ]);
        $I->seeResponseCodeIs(201);

        $I->canSeeRecord(Order::class, [
            'datetime'            => date("Y-m-d H:i:00"),
            'company_customer_id' => $companyCustomer->id,
            'staff_id'            => $staff->id,
            'division_id'         => $division->id
        ]);
    }
}
