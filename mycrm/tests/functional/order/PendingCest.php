<?php

namespace api\tests\order;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\order\Order;
use core\models\Staff;
use core\models\StaffDivisionMap;
use core\models\user\User;
use FunctionalTester;

class PendingCest
{
    private $responseFormat
        = [
            'id'    => 'integer',
            'className'  => 'string|null',
            'color'  => 'string|null',
            'company_customer_id' => 'integer',
            'company_cash_id' => 'integer',
            'datetime' => 'string',
            'division_id' => 'integer',
            'editable' => 'boolean',
            'end' => 'string',
            'hours_before' => 'integer',
            'insurance_company_id' => 'integer|null',
            'note' => 'string|null',
            'customer' => 'array',
            'referrer_id' => 'integer|null',
            'resourceId' => 'integer',
            'staff_id' => 'integer',
            'start' => 'string',
            'status' => 'integer',
            'status_name' => 'string',
            'title' => 'string'
        ];

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Pending order index');
        $I->sendGET('pending-order');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $this->getOrder($I, $user);

        $I->sendGET('pending-order?expand=customer,title');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I){
        $I->wantToTest('Pending order view');
        $I->sendGET('pending-order/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $order = $this->getOrder($I, $user);

        $I->sendGET("pending-order/{$order->id}?expand=customer,title");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Pending order create');
        $I->sendPOST('pending-order');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id,
        ]);

        $staff = $I->getFactory()->create(Staff::class, [
            'user_id' => $user->id
        ]);

        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $user->company_id,
        ]);

        $datetime = gmdate('Y-m-d');

        $I->sendPOST('pending-order?expand=customer,title', [
            'company_customer_id' => $companyCustomer->id,
            'customer_name' => $companyCustomer->customer->name,
            'customer_phone' => $companyCustomer->customer->phone,
            'note' => 'note',
            'date' => $datetime,
            'staff_id' => $staff->id,
            'division_id' => $division->id
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Pending order update');
        $I->sendPUT('pending-order/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $order = $this->getOrder($I, $user);

        //  Check w/o permission
        $I->sendPUT("order/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($user, 'orderUpdate');

        //  Check permitted
        $I->sendPUT("pending-order/{$order->id}?expand=customer,title", [
            'note' => 'changed note',
            'customer_name' => $I->getFaker()->name,
            'date' => date('Y-m-d'),
            'staff_id' => $order->staff_id,
            'division_id' => $order->division_id
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function enable(FunctionalTester $I)
    {
        $I->wantToTest('Order enable');
        $I->sendPOST('pending-order/enabled/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $user->company_id,
        ]);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_WAITING]);
        $I->sendPOST("pending-order/enabled/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($user, 'orderUpdate');
        $order = $this->getOrder($I, $user);

        $I->sendPOST("pending-order/enabled/{$order->id}?expand=customer,title",[
            'start' => date('Y-m-d H:i:s')
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
        $I->canSeeRecord(Order::class, [
            'id'     => $order->id,
            'status' => OrderConstants::STATUS_ENABLED,
        ]);
    }

    public function delete(FunctionalTester $I)
    {
        $I->wantToTest('Pending order delete');
        $I->sendDELETE('pending-order/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $user->company_id,
        ]);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_WAITING]);
        $I->sendDELETE("pending-order/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($user, 'orderUpdate');
        $order = $this->getOrder($I, $user);

        $I->sendDELETE("pending-order/{$order->id}");
        $I->seeResponseCodeIs(204);
        $I->cantSeeRecord(Order::class, [
            'id'     => $order->id,
            'status' => OrderConstants::STATUS_ENABLED,
        ]);
    }

    private function getOrder(FunctionalTester $I, User $user)
    {
        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id,
        ]);

        $staff = $I->getFactory()->create(Staff::class, [
            'user_id' => $user->id
        ]);

        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $user->company_id,
        ]);

        $staffDivisionMap = $I->getFactory()->create(StaffDivisionMap::class, [
            'staff_id' => $staff->id,
            'division_id' => $division->id
        ]);

        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $division->id,
            'status' => OrderConstants::STATUS_WAITING,
            'company_customer_id' => $companyCustomer->id,
            'staff_id' => $staff->id
        ]);

        return $order;
    }
}
