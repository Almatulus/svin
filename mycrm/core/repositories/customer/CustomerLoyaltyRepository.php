<?php
/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 15.03.2018
 * Time: 9:35
 */

namespace core\repositories\customer;

use core\models\customer\CustomerLoyalty;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

/**
 * Class CustomerLoyaltyRepository
 * @package core\repositories\customer
 */
class CustomerLoyaltyRepository extends BaseRepository
{
    /**
     * @param int $id
     * @return CustomerLoyalty
     */
    public function findById(int $id)
    {
        if (!$model = CustomerLoyalty::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param int $company_id
     * @return array|CustomerLoyalty[]
     */
    public function findByCompany(int $company_id)
    {
        return $this->find()->company($company_id)->all();
    }

    /**
     * @return \core\models\customer\query\CustomerLoyaltyQuery
     */
    public function find()
    {
        return CustomerLoyalty::find();
    }
}