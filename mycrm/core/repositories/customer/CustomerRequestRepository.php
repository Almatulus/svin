<?php

namespace core\repositories\customer;

use core\helpers\order\OrderConstants;
use core\models\customer\CustomerRequest;
use core\repositories\exceptions\NotFoundException;

class CustomerRequestRepository
{
    /**
     * @param $id
     * @return CustomerRequest
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = CustomerRequest::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $id
     * @return CustomerRequest
     * @throws NotFoundException
     */
    public function findBySmscId($id)
    {
        if (!$model = CustomerRequest::find()->where(['smsc_id' => $id])->one()) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param CustomerRequest $model
     */
    public function add(CustomerRequest $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param CustomerRequest $model
     */
    public function edit(CustomerRequest $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param CustomerRequest $model
     */
    public function delete(CustomerRequest $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param $from
     * @param $to
     * @return int
     */
    public function getSentCount($from, $to)
    {
        return CustomerRequest::find()
            ->where(['>=', 'created_time', $from])
            ->andWhere(['<=', 'created_time', $to])
            ->andWhere(['NOT IN', 'company_id', OrderConstants::STATISTICS_EXCLUDED_COMPANIES])
            ->count();
    }
}