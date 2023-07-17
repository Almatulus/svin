<?php

namespace api\tests\statistic;

use core\helpers\order\OrderConstants;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\order\Order;
use core\models\order\OrderService;
use FunctionalTester;

class ServiceCest
{
    private $responseFormat = [
        'id'          => 'integer',
        'name'        => 'string',
        'ordersCount' => 'integer',
        'revenue'     => 'integer|string'
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
        $I->wantToTest("Statistic service index");

        $I->sendGET('statistic/service');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('statistic/service');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([]);

        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $services = $I->getFactory()->seed(2, DivisionService::class);
        foreach ($services as $service) {
            $service->link('divisions', $division);
        }
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $division->id,
            'status'      => OrderConstants::STATUS_FINISHED
        ]);
        foreach ($services as $divisionService) {
            $I->getFactory()->create(OrderService::class, [
                'order_id'            => $order->id,
                'division_service_id' => $divisionService->id
            ]);
        }

        $fromDate = (new \DateTimeImmutable())->modify("-7 days");
        $toDate = (new \DateTimeImmutable())->modify("+7 days");

        $I->sendGET("statistic/service?from={$fromDate->format('Y-m-d')}&to={$toDate->format('Y-m-d')}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    // tests
    public function top(FunctionalTester $I)
    {
        $I->wantToTest("Statistic service top");

        $I->sendGET('statistic/service/top');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendGET('statistic/service/top');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'maxRevenue'   => 'null',
            'mostPopular'  => 'null',
            'leastPopular' => 'null',
        ]);

        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $services = $I->getFactory()->seed(2, DivisionService::class);
        foreach ($services as $service) {
            $service->link('divisions', $division);
        }
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $division->id,
            'status'      => OrderConstants::STATUS_FINISHED
        ]);
        foreach ($services as $divisionService) {
            $I->getFactory()->create(OrderService::class, [
                'order_id'            => $order->id,
                'division_service_id' => $divisionService->id
            ]);
        }

        $I->sendGET('statistic/service/top');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'maxRevenue'   => $this->responseFormat,
            'mostPopular'  => $this->responseFormat,
            'leastPopular' => $this->responseFormat
        ]);
    }
}
