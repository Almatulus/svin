<?php

namespace api\tests\statistic;

use core\helpers\order\OrderConstants;
use core\models\division\Division;
use core\models\InsuranceCompany;
use core\models\order\Order;
use core\models\order\OrderService;
use FunctionalTester;

class InsuranceCest
{
    private $responseFormat
        = [
            'id'                   => 'integer',
            'datetime'             => 'string',
            'staff_id'             => 'integer',
            'division_id'          => 'integer',
            'company_customer_id'  => 'integer',
            'status'               => 'integer',
            'note'                 => 'string|null',
            'hours_before'         => 'integer',
            'color'                => 'string|null',
            'start'                => 'string',
            'end'                  => 'string',
            "company_cash_id"      => 'integer',
            "insurance_company_id" => 'integer|null',
            "referrer_id"          => 'integer|null',
            "resourceId"           => 'integer|null',
            "className"            => 'string|null',
            "editable"             => 'boolean',
            'services'             => 'array',
            'payments'             => 'array',
        ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Statistic insurance index');

        $I->sendGET('statistic/insurance');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->assignRoles($user, 'company');
        \Yii::$app->set('user', $user);

        $insuranceCompany = $I->getFactory()->create(InsuranceCompany::class);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $order = $I->getFactory()->create(Order::class, [
            'status'               => OrderConstants::STATUS_FINISHED,
            'division_id'          => $division->id,
            'insurance_company_id' => $insuranceCompany->id
        ]);
        $orderService = $I->getFactory()->create(OrderService::class, ['order_id' => $order->id]);

        $I->sendGET('statistic/insurance?expand=services,payments');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function export(FunctionalTester $I)
    {
        $I->wantToTest('Statistic insurance export');

        $I->sendGET('statistic/insurance/export');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->assignRoles($user, 'company');
        \Yii::$app->set('user', $user);

        $insuranceCompany = $I->getFactory()->create(InsuranceCompany::class);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $order = $I->getFactory()->create(Order::class, [
            'status'               => OrderConstants::STATUS_FINISHED,
            'division_id'          => $division->id,
            'insurance_company_id' => $insuranceCompany->id
        ]);
        $orderService = $I->getFactory()->create(OrderService::class, ['order_id' => $order->id]);


        $I->sendGET('statistic/insurance/export');
        $I->seeResponseCodeIs(200);
    }
}
