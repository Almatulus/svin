<?php

namespace core\repositories;

use core\models\finance\Payroll;
use core\repositories\exceptions\NotFoundException;

class PayrollRepository
{
    /**
     * @param $id
     * @return Payroll
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = Payroll::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param Payroll $model
     * @throws \Exception
     * @throws \Throwable
     */
    public function add(Payroll $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param Payroll $model
     * @throws \Exception
     * @throws \Throwable
     */
    public function edit(Payroll $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param Payroll $model
     * @throws \Exception
     * @throws \Throwable
     */
    public function delete(Payroll $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}