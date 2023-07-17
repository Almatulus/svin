<?php

namespace core\repositories\company;

use core\models\company\Insurance;
use core\repositories\exceptions\NotFoundException;

class InsuranceRepository
{
    /**
     * @param $id
     * @return Insurance
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = Insurance::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param int $company_id
     * @param int $insurance_company_id
     * @return Insurance
     */
    public function findByInsuranceCompany(int $company_id, int $insurance_company_id)
    {
        $model = Insurance::findOne([
            'company_id'           => $company_id,
            'insurance_company_id' => $insurance_company_id
        ]);
        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(Insurance $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(Insurance $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(Insurance $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}