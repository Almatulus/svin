<?php

namespace services;


use Codeception\Specify;
use Codeception\Util\Stub;
use core\models\customer\Customer;
use core\models\customer\CustomerContact;
use core\services\customer\ContactService;

class ContactServiceTest extends \Codeception\Test\Unit
{
    use Specify;

    /**
     * @var ContactService
     */
    private $service;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->service = Stub::make(ContactService::class);
    }

    protected function _after()
    {
    }

    // tests
    public function testCreate()
    {
        $customer = $this->tester->getFactory()->create(Customer::class);
        $contact = $this->tester->getFactory()->create(Customer::class);

        // successful creation
        $this->service->create($customer, $contact);
        $this->tester->canSeeRecord(CustomerContact::class, [
            'customer_id' => $customer->id,
            'contact_id'  => $contact->id,
        ]);

        // no duplicate contact
        $this->service->create($contact, $customer);
        $this->tester->cantSeeRecord(CustomerContact::class, [
            'customer_id' => $contact->id,
            'contact_id'  => $customer->id,
        ]);

        $this->specify('Customer cannot add himself to contacts', function () use ($customer) {
            $this->expectException(\DomainException::class);
            $this->service->create($customer, $customer);
        });
    }
}