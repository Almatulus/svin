<?php

namespace core\repositories\customer;

use core\models\customer\CustomerCategory;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class CompanyCustomerCategoryRepository extends BaseRepository
{
    /**
     * @param $id
     * @return CustomerCategory
     */
    public function find($id)
    {
        if (!$model = CustomerCategory::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }
}