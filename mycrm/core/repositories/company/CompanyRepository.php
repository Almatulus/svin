<?php

namespace core\repositories\company;

use core\models\company\Company;
use core\models\company\TariffPayment;
use core\repositories\exceptions\NotFoundException;

class CompanyRepository
{
    /**
     * @param $id
     * @return Company
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = Company::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(Company $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(Company $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(Company $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    /**
     * @param $id
     * @return TariffPayment
     * @throws NotFoundException
     */
    public function findTariffPayment($id)
    {
        if (!$model = TariffPayment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }
}