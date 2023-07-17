<?php

namespace modules\order\forms;

use core\forms\order\OrderCreateForm;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\finance\CompanyCash;
use core\models\Payment;
use core\models\Staff;
use core\models\warehouse\Product;

class OrderCreateFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var OrderCreateForm
     */
    private $_form;

    /**
     * @var CompanyCash
     */
    private $companyCash;
    /**
     * @var CompanyCustomer
     */
    private $companyCustomer;
    /**
     * @var Division
     */
    private $division;
    /**
     * @var DivisionService[]
     */
    private $divisionServices;
    /**
     * @var Payment[]
     */
    private $payments;
    /**
     * @var Product[]
     */
    private $products;
    /**
     * @var Staff
     */
    private $staff;

    protected function _before()
    {
        $this->_form = new OrderCreateForm();

        $this->division = $this->tester->getFactory()->create(Division::class);
        $this->staff = $this->tester->getFactory()->create(Staff::class);

        $this->companyCash = $this->tester->getFactory()->create(CompanyCash::class, [
            'division_id' => $this->division->id
        ]);
        $this->companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $this->division->company_id
        ]);
        $this->divisionServices = [
            $this->tester->getFactory()->create(DivisionService::class),
            $this->tester->getFactory()->create(DivisionService::class)
        ];
        foreach ($this->divisionServices as $service) {
            $service->link('divisions', $this->division);
        }
        $this->payments = [
            $this->tester->getFactory()->create(Payment::class),
            $this->tester->getFactory()->create(Payment::class)
        ];
        $this->products = [
            $this->tester->getFactory()->create(Product::class, ['division_id' => $this->division->id]),
            $this->tester->getFactory()->create(Product::class, ['division_id' => $this->division->id])
        ];
    }

    protected function _after()
    {
    }

    // tests
    public function testValidationRequired()
    {
        expect("model is not valid", $this->_form->validate())->false();
        expect("company_cash_id error", $this->_form->getErrors())->hasKey("company_cash_id");
        expect("customer_name error", $this->_form->getErrors())->hasKey("customer_name");
        expect("datetime error", $this->_form->getErrors())->hasKey("datetime");
        expect("division_id error", $this->_form->getErrors())->hasKey("division_id");
        expect("hours_before error", $this->_form->getErrors())->hasKey("hours_before");
        expect("staff_id error", $this->_form->getErrors())->hasKey("staff_id");
        expect("services error", $this->_form->getErrors())->hasKey("services");

        $this->_form->setAttributes($this->getValidAttributes());
        expect("model is valid", $this->_form->validate())->true();
        expect("company_cash_id error", $this->_form->getErrors())->hasntKey("company_cash_id");
        expect("customer_name error", $this->_form->getErrors())->hasntKey("customer_name");
        expect("datetime error", $this->_form->getErrors())->hasntKey("datetime");
        expect("division_id error", $this->_form->getErrors())->hasntKey("division_id");
        expect("hours_before error", $this->_form->getErrors())->hasntKey("hours_before");
        expect("staff_id error", $this->_form->getErrors())->hasntKey("staff_id");
        expect("services error", $this->_form->getErrors())->hasntKey("services");

    }

    public function testValidationDatetimeFormat()
    {
        $this->_form->setAttributes(['datetime' => date("Y-m-d H:i:s")]);
        expect("model is not valid", $this->_form->validate(['datetime']))->false();

        $this->_form->setAttributes(['datetime' => date("Y-m-d H:i")]);
        expect("model is not valid", $this->_form->validate(['datetime']))->true();
    }

    public function testValidationPhoneFormat()
    {
        $this->_form->setAttributes(['customer_phone' => "+7 702 892 05"]);
        expect("model is not valid", $this->_form->validate(['customer_phone']))->false();

        $this->_form->setAttributes(['customer_phone' => "702 892 05 00"]);
        expect("model is not valid", $this->_form->validate(['customer_phone']))->false();
    }

    public function testValidationServices()
    {
        // test non-existing division service
        $this->_form->services = [
            [
                'division_service_id' => 0,
                'duration'            => $this->divisionServices[0]->average_time,
                'discount'            => rand(0, 100),
                'price'               => $this->divisionServices[0]->price,
                'quantity'            => rand(1, 10)
            ]
        ];
        expect("model is not valid", $this->_form->validate(['services']))->false();

        // test negative price
        $this->_form->services = [
            [
                'division_service_id' => $this->divisionServices[0]->id,
                'duration'            => $this->divisionServices[0]->average_time,
                'discount'            => rand(0, 100),
                'price'               => -100,
                'quantity'            => rand(1, 10)
            ]
        ];
        expect("model is not valid", $this->_form->validate(['services']))->false();

        // test negative discount
        $this->_form->services = [
            [
                'division_service_id' => $this->divisionServices[0]->id,
                'duration'            => $this->divisionServices[0]->average_time,
                'discount'            => -1,
                'quantity'            => rand(1, 10),
                'price'               => $this->divisionServices[0]->price
            ]
        ];
        expect("model is not valid", $this->_form->validate(['services']))->false();

        // test zero quantity
        $this->_form->services = [
            [
                'division_service_id' => $this->divisionServices[0]->id,
                'duration'            => $this->divisionServices[0]->average_time,
                'discount'            => rand(0, 100),
                'quantity'            => 0,
                'price'               => $this->divisionServices[0]->price
            ]
        ];
        expect("model is not valid", $this->_form->validate(['services']))->false();
    }

    public function testValidationProducts()
    {
        // test non-existing product
        $this->_form->products = [
            [
                'product_id' => -1,
                'quantity'   => $this->products[0]->quantity - 1,
                'price'      => $this->products[0]->price
            ]
        ];
        expect("model is not valid", $this->_form->validate(['products'], true))->false();

        // test negative quantity
        $this->_form->products = [
            [
                'product_id' => $this->products[0]->id,
                'quantity'   => -2,
                'price'      => $this->products[0]->price
            ]
        ];
        expect("model is not valid", $this->_form->validate(['products'], true))->false();
    }

    public function testValidationPayments()
    {
        // test non-existing payment
        $this->_form->payments = [
            [
                'payment_id' => 0,
                'amount'     => 100
            ],
        ];
        expect("model is not valid", $this->_form->validate(['payments'], true))->false();

        // test negative payment value
        $this->_form->payments = [
            [
                'payment_id' => $this->payments[0]->id,
                'amount'     => -100
            ],
        ];
        expect("model is not valid", $this->_form->validate(['payments'], true))->false();
    }

    public function testValidateStaff()
    {
        $this->_form->staff_id = 0;
        expect("staff doesn't exist", $this->_form->validate(['staff_id'], true))->false();

        $this->_form->staff_id = "asd";
        expect("staff_id is string, has to be integer", $this->_form->validate(['staff_id'], true))->false();

        $this->_form->staff_id = false;
        expect("staff_id is boolean, has to be integer", $this->_form->validate(['staff_id'], true))->false();

        $this->_form->staff_id = $this->staff->id;
        expect("staff is valid", $this->_form->validate(['staff_id'], true))->true();
    }

    private function getValidAttributes()
    {
        return [
            'customer_name'   => $this->companyCustomer->customer->name,
            'customer_phone'  => $this->companyCustomer->customer->phone,
            'company_cash_id' => $this->companyCash->id,
            'datetime'        => date("Y-m-d H:i"),
            'division_id'     => $this->division->id,
            'hours_before'    => 0,
            'staff_id'        => $this->staff->id,
            'products'        => $this->getValidProducts(),
            'services'        => $this->getValidServices(),
            'payments'        => [
                [
                    'payment_id' => $this->payments[0]->id,
                    'amount'     => 100
                ],
                [
                    'payment_id' => $this->payments[0]->id,
                    'amount'     => 200
                ],
            ],
        ];
    }

    private function getValidServices()
    {
        return [
            [
                'division_service_id' => $this->divisionServices[0]->id,
                'duration'            => $this->divisionServices[0]->average_time,
                'discount'            => rand(0, 100),
                'quantity'            => rand(1, 10),
                'price'               => $this->divisionServices[0]->price
            ],
            [
                'division_service_id' => $this->divisionServices[1]->id,
                'duration'            => $this->divisionServices[1]->average_time,
                'discount'            => rand(0, 100),
                'quantity'            => rand(1, 10),
                'price'               => $this->divisionServices[1]->price
            ],
        ];
    }

    private function getValidProducts()
    {
        return [
            $this->divisionServices[0]->id => [
                [
                    'division_service_id' => $this->divisionServices[0]->id,
                    'product_id'          => $this->products[0]->id,
                    'quantity'            => $this->products[0]->quantity - 1,
                    'price'               => $this->products[0]->price
                ]
            ],
            $this->divisionServices[1]->id => [
                [
                    'division_service_id' => $this->divisionServices[1]->id,
                    'product_id'          => $this->products[1]->id,
                    'quantity'            => $this->products[1]->quantity - 1,
                    'price'               => $this->products[1]->price
                ]
            ]
        ];
    }
}