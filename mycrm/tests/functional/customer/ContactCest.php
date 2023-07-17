<?php

namespace tests\functional\customer;

use FunctionalTester;
use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerContact;

class ContactCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $user = $I->login();

        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id
        ]);

        $I->getFactory()->seed(2, CustomerContact::class, [
            'customer_id' => $companyCustomer->customer->id
        ]);

        $I->sendGET("customer/{$companyCustomer->id}/contact");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'customer_id' => 'integer',
            'contact_id'  => 'integer',
        ]);
    }
}
