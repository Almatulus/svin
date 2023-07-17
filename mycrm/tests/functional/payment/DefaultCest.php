<?php

namespace api\tests\schedule;

use core\models\Payment;
use FunctionalTester;

class DefaultCest
{
    private $responseFormat
        = [
            'id'    => 'integer',
            'name'  => 'string',
            'type'  => 'integer|null'
        ];

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->getFactory()->seed(5,Payment::class, []);

        $I->sendGET('payment');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I){
        $payment = $I->getFactory()->create(Payment::class, []);

        $I->sendGET("payment/{$payment->id}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
