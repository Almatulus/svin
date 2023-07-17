<?php

namespace api\tests\order;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\order\Order;
use core\models\order\OrderService;
use core\models\Staff;
use core\models\user\User;
use core\models\warehouse\Product;
use FunctionalTester;

class DefaultCest
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
            'number'               => 'integer',
            'hours_before'         => 'integer',
            'color'                => 'string|null',
            'title'                => 'string',
            'start'                => 'string',
            'end'                  => 'string',
            "company_cash_id"      => 'integer',
            "insurance_company_id" => 'integer|null',
            "referrer_id"          => 'integer|null',
            "resourceId"           => 'integer|null',
            "className"            => 'string|null',
            "editable"             => 'boolean',
            'payment_difference'   => 'integer'
        ];

    private $user;

    private $division;

    public function _before(FunctionalTester $I)
    {
        $this->user = $I->getFactory()->create(User::class);
        $this->division = $I->getFactory()->create(Division::class, [
            'company_id' => $this->user->company_id,
        ]);
    }

    public function _after(FunctionalTester $I)
    {

    }

    // tests
    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Order create');
        $I->checkLogin('order');

        $I->login($this->user);

        $I->sendPOST("order");
        $I->seeResponseCodeIs(422);

        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $this->user->company_id,
        ]);
        $staff = $I->getFactory()->create(Staff::class);
        $service = $I->getFactory()->create(DivisionService::class);
        $service->link('divisions', $this->division);
        $product = $I->getFactory()->create(Product::class);
        $datetime = gmdate('Y-m-d H:i');

        $I->sendPOST("order?expand=title", [
            'customer_name'        => $companyCustomer->customer->name,
            'customer_surname'     => $companyCustomer->customer->lastname,
            'customer_patronymic'  => $companyCustomer->customer->patronymic,
            'customer_phone'       => $companyCustomer->customer->phone,
            'customer_birth_date'  => $companyCustomer->customer->birth_date,
            'customer_medical_record_id' => $companyCustomer->medical_record_id,
            'customer_gender'      => $companyCustomer->customer->gender,
            'company_cash_id'      => $this->division->companyCash->id,
            'datetime'             => $datetime,
            'division_id'          => $this->division->id,
            'staff_id'             => $staff->id,
            'hours_before'         => 0,
            'customer_source_name' => 'some source name',
            'referrer_name'        => 'some referrer name',
            'products'             => [
                [
                    'quantity'   => 1,
                    'price'      => $product->price,
                    'product_id' => $product->id,
                ],
            ],
            'services'             => [
                [
                    'division_service_id' => $service->id,
                    'discount'            => 0,
                    'duration'            => $service->average_time,
                    'price'               => $service->price,
                    'quantity'            => 1,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(array_merge($this->responseFormat, ['referrer_id' => 'integer']));
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Order update');
        $I->sendPUT('order');
        $I->seeResponseCodeIs(401);

        $I->login($this->user);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_ENABLED]);
        $I->sendPUT("order/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($this->user, 'orderUpdate');
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_ENABLED,
        ]);
        $product = $I->getFactory()->create(Product::class);
        $service = $I->getFactory()->create(DivisionService::class);
        $service->link('divisions', $this->division);
        $datetime = gmdate('Y-m-d H:i');

        $I->sendPUT("order/{$order->id}", [
            'datetime'      => $datetime,
            'services'      => [
                [
                    'division_service_id' => $service->id,
                    'discount'            => 0,
                    'duration'            => $service->average_time,
                    'price'               => $service->price,
                    'quantity'            => 1,
                ],
            ],
            'products'      => [
                [
                    'quantity'   => 1,
                    'price'      => $product->price,
                    'product_id' => $product->id,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(422);

        $order->staff->link("divisionServices", $service);
        $I->sendPUT("order/{$order->id}?expand=title", [
            'datetime'             => $datetime,
            'customer_source_name' => 'some source name',
            'referrer_name'        => 'some referrer name',
            'services'             => [
                [
                    'division_service_id' => $service->id,
                    'discount'            => 0,
                    'duration'            => $service->average_time,
                    'price'               => $service->price,
                    'quantity'            => 1,
                ],
            ],
            'products'             => [
                [
                    'quantity'   => 1,
                    'price'      => $product->price,
                    'product_id' => $product->id,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(array_merge($this->responseFormat, ['referrer_id' => 'integer']));
    }

    public function cancel(FunctionalTester $I)
    {
        $I->wantToTest('Order cancel');
        $I->sendPOST('order/cancel');
        $I->seeResponseCodeIs(401);

        $I->login($this->user);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_ENABLED]);
        $I->sendPOST("order/cancel/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($this->user, 'orderDelete');
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_ENABLED,
        ]);

        $I->sendPOST("order/cancel/{$order->id}?expand=title");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
        $I->canSeeRecord(Order::class, [
            'id'     => $order->id,
            'status' => OrderConstants::STATUS_CANCELED,
        ]);
    }

    public function delete(FunctionalTester $I)
    {
        $I->wantToTest('Order delete');
        $I->sendDELETE('order');
        $I->seeResponseCodeIs(401);

        $I->login($this->user);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_ENABLED]);
        $I->sendDELETE("order/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($this->user, 'orderDelete');
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_ENABLED,
        ]);

        $I->sendDELETE("order/{$order->id}");
        $I->seeResponseCodeIs(200);
        $I->canSeeRecord(Order::class, [
            'id'     => $order->id,
            'status' => OrderConstants::STATUS_DISABLED,
        ]);
    }

    public function checkout(FunctionalTester $I)
    {
        $I->wantToTest('Order checkout');
        $I->sendPOST('order/checkout');
        $I->seeResponseCodeIs(401);

        $I->login($this->user);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_ENABLED]);
        $I->sendPOST("order/checkout/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($this->user, 'orderUpdate');
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_ENABLED,
        ]);
        $orderService = $I->getFactory()->create(OrderService::class, [
            'order_id' => $order->id,
        ]);
        $I->sendPOST("order/checkout/{$order->id}");
        $I->seeResponseCodeIs(422);

        $order->staff->link("divisionServices", $orderService->divisionService);
        $I->sendPOST("order/checkout/{$order->id}?expand=title");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
        $I->canSeeRecord(Order::class, [
            'id'     => $order->id,
            'status' => OrderConstants::STATUS_FINISHED,
        ]);
    }

    public function events(FunctionalTester $I)
    {
        $I->wantToTest('Order events');
        $I->checkLogin('order/events');

        $I->getFactory()->seed(5, Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_ENABLED,
        ]);
        $I->login($this->user);
        $I->sendGET('order/events', [
            'start' => (new \DateTime())->format("Y-m-d"),
            'end'   => (new \DateTime())->modify("+1 day")->format("Y-m-d"),
        ]);
        $I->seeResponseCodeIs(200);
    }

    public function updateDuration(FunctionalTester $I)
    {
        $I->wantToTest('Order update duration');
        $I->sendPUT('order/update-duration');
        $I->seeResponseCodeIs(404);

        $I->login($this->user);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_ENABLED]);
        $I->sendPost("order/update-duration/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($this->user, 'orderUpdate');
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_ENABLED,
        ]);
        $I->getFactory()->create(OrderService::class, [
            'order_id' => $order->id,
        ]);
        $end = (new \DateTime('now', new \DateTimeZone('Asia/Almaty')))->modify("+5 hours");

        $I->sendPost("order/update-duration/{$order->id}?expand=title",
            ['end' => $end->format("Y-m-d H:i:s")]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function drop(FunctionalTester $I)
    {
        $I->wantToTest('Order drop');
        $I->sendPUT('order/drop');
        $I->seeResponseCodeIs(401);

        $I->login($this->user);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_ENABLED]);
        $I->sendPost("order/drop/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($this->user, 'orderUpdate');
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_ENABLED,
        ]);
        $orderService = $I->getFactory()->create(OrderService::class, [
            'order_id' => $order->id,
        ]);
        $staff = $I->getFactory(Staff::class)->create(Staff::class);
        $start = (new \DateTime())->modify("+1 day +5 hours");

        $I->sendPOST("order/drop/{$order->id}", [
            'start' => $start->format("Y-m-d H:i:s"),
            'staff' => $staff->id,
        ]);
        $I->seeResponseCodeIs(422);

        $staff->link('divisionServices', $orderService->divisionService);
        $I->sendPOST("order/drop/{$order->id}?expand=title", [
            'start' => $start->format("Y-m-d H:i:s"),
            'staff' => $staff->id,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function enable(FunctionalTester $I)
    {
        $I->wantToTest('Order enable');
        $I->sendPOST('order/enable');
        $I->seeResponseCodeIs(401);

        $I->login($this->user);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_CANCELED]);
        $I->sendPOST("order/enable/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($this->user, 'orderUpdate');
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_CANCELED,
        ]);

        $I->sendPOST("order/enable/{$order->id}?expand=title");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
        $I->canSeeRecord(Order::class, [
            'id'     => $order->id,
            'status' => OrderConstants::STATUS_ENABLED,
        ]);
    }

    public function return(FunctionalTester $I)
    {
        $I->wantToTest('Order return');
        $I->sendPOST('order/return');
        $I->seeResponseCodeIs(401);

        $I->login($this->user);
        $order = $I->getFactory()->create(Order::class,
            ['status' => OrderConstants::STATUS_FINISHED]);
        $I->sendPOST("order/return/{$order->id}");
        $I->seeResponseCodeIs(404);

        $I->assignPermission($this->user, 'orderUpdate');
        $order = $I->getFactory()->create(Order::class, [
            'division_id' => $this->division->id,
            'status'      => OrderConstants::STATUS_FINISHED,
        ]);

        $I->sendPOST("order/return/{$order->id}?expand=title");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
        $I->canSeeRecord(Order::class, [
            'id'     => $order->id,
            'status' => OrderConstants::STATUS_ENABLED,
        ]);
    }

    public function export(FunctionalTester $I)
    {
        // I can't export Order without authorization
        $I->wantToTest('Order export');
        $I->sendPOST('order/export');
        $I->seeResponseCodeIs(401);

        $I->login();

        // I seed "my" Orders
        $I->getFactory()->seed(3, Order::class, [
            'division_id' => $this->division->id
        ]);

        // I seed "foreign" Orders
        $I->getFactory()->seed(3, Order::class, []);

        $I->sendPOST('customer/export');
        $I->seeResponseCodeIs(200);
    }

    /**
     * @group overlapping
     * @param FunctionalTester $I
     */
    public function overlapping(FunctionalTester $I)
    {
        $I->wantTo('Overlapping orders');
        $I->sendPOST('order/overlapping');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, ['company_id' => $user->company_id]);
        $division = $I->getFactory()->create(Division::class);
        $staff = $I->getFactory()->create(Staff::class);
        $datetime = (new \DateTimeImmutable());
        $datetime->setTime(12, 30, 0);
        $I->sendPOST('order/overlapping', [
            'division_id' => $division->id,
            'staff_id'    => $staff->id,
            'start'       => $datetime->format("Y-m-d H:i"),
            'end'         => $datetime->modify("+30 minutes")->format("Y-m-d H:i"),
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(['overlapping' => 'boolean']);
        $I->seeResponseContains('false');

        // order starts when other starts
        $order = $I->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'division_id'         => $division->id,
            'staff_id'            => $staff->id,
            'datetime'            => $datetime->format("Y-m-d H:i"),
            'duration'            => 30,
            'status'              => OrderConstants::STATUS_ENABLED
        ]);
        $I->sendPOST('order/overlapping', [
            'division_id' => $division->id,
            'staff_id'    => $staff->id,
            'start'       => $datetime->format("Y-m-d H:i"),
            'end'         => $datetime->modify('+15 minutes')->format("Y-m-d H:i"),
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(['overlapping' => 'boolean']);
        $I->seeResponseContains('true');

        // order's start is during another order
        $datetime->modify("+1 day");
        $order = $I->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'duration'            => 15,
            'division_id'         => $division->id,
            'staff_id'            => $staff->id,
            'datetime'            => $datetime->format("Y-m-d H:i"),
            'status'              => OrderConstants::STATUS_FINISHED
        ]);
        $I->sendPOST('order/overlapping', [
            'division_id' => $division->id,
            'staff_id'    => $staff->id,
            'start'       => $datetime->modify("+10 minutes")->format("Y-m-d H:i"),
            'end'         => $datetime->modify('+20 minutes')->format("Y-m-d H:i"),
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(['overlapping' => 'boolean']);
        $I->seeResponseContains('true');

        // order's end is during another order
        $datetime->modify("+1 day");
        $order = $I->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'duration'            => 15,
            'division_id'         => $division->id,
            'staff_id'            => $staff->id,
            'datetime'            => $datetime->format("Y-m-d H:i"),
            'status'              => OrderConstants::STATUS_FINISHED
        ]);
        $I->sendPOST('order/overlapping', [
            'division_id' => $division->id,
            'staff_id'    => $staff->id,
            'start'       => $datetime->modify("-10 minutes")->format("Y-m-d H:i"),
            'end'         => $datetime->modify("+10 minutes")->format("Y-m-d H:i"),
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(['overlapping' => 'boolean']);
        $I->seeResponseContains('true');
    }
}