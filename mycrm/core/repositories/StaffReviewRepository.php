<?php

namespace core\repositories;

use core\models\StaffReview;
use core\repositories\exceptions\NotFoundException;

class StaffReviewRepository
{
    /**
     * @param $id
     * @return StaffReview
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$review = StaffReview::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $review;
    }

    /**
     * @param integer $customer_id
     * @param integer $staff_id
     * @return StaffReview
     */
    public function findByCustomerAndStaff($customer_id, $staff_id)
    {
        /* @var StaffReview $review */
        if (!$review = StaffReview::find()->customer($customer_id)->staff($staff_id)->one()) {
            throw new NotFoundException('Model not found.');
        }
        return $review;
    }

    public function add(StaffReview $review)
    {
        if (!$review->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$review->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(StaffReview $review)
    {
        if ($review->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($review->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(StaffReview $review)
    {
        if (!$review->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}