<?php

namespace services;

use core\models\customer\Customer;
use core\models\Staff;
use core\models\StaffReview;
use core\services\StaffReviewService;

class StaffReviewServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var StaffReviewService */
    protected $service;

    public function testAdd()
    {
        $customer = $this->tester->getFactory()->create(Customer::class);
        $staff = $this->tester->getFactory()->create(Staff::class);

        $value = $this->tester->getFaker()->numberBetween(1, 5);
        $comment = $this->tester->getFaker()->text(20);

        $this->service->add($customer->id, $staff->id, $value, $comment);

        $this->tester->canSeeRecord(StaffReview::class, [
            'customer_id' => $customer->id,
            'staff_id'    => $staff->id,
            'comment'     => $comment,
            'value'       => $value
        ]);
    }

    public function testEdit()
    {
        $staffReview = $this->tester->getFactory()->create(StaffReview::class);

        $value = $this->tester->getFaker()->numberBetween(1, 5);
        $comment = $this->tester->getFaker()->text(20);
        $this->service->edit($staffReview->customer_id, $staffReview->staff_id, $value, $comment);

        $this->tester->canSeeRecord(StaffReview::class, [
            'id'      => $staffReview->id,
            'comment' => $comment,
            'value'   => $value
        ]);
    }

    // tests

    protected function _before()
    {
        $this->service = \Yii::createObject(StaffReviewService::class);
    }

    protected function _after()
    {
    }
}