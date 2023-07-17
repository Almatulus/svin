<?php

namespace core\services;

use core\models\StaffReview;
use core\repositories\StaffReviewRepository;

class StaffReviewService
{
    private $staffReviewRepository;

    public function __construct(StaffReviewRepository $staffReviewRepository)
    {
        $this->staffReviewRepository = $staffReviewRepository;
    }

    public function add($customer_id, $staff_id, $value, $comment)
    {
        $interview = StaffReview::add($customer_id, $staff_id, $value, $comment);
        $this->staffReviewRepository->add($interview);
        return $interview;
    }

    public function edit($customer_id, $staff_id, $value, $comment)
    {
        $interview = $this->staffReviewRepository->findByCustomerAndStaff($customer_id, $staff_id);
        $interview->edit($value, $comment);
        $this->staffReviewRepository->edit($interview);
    }
}
