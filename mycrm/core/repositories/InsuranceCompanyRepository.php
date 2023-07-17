<?php

namespace core\repositories;

use core\models\InsuranceCompany;
use core\repositories\exceptions\NotFoundException;

class InsuranceCompanyRepository
{
    /**
     * @param $id
     *
     * @return InsuranceCompany
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (($model = InsuranceCompany::findOne($id)) === false) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param InsuranceCompany $model
     */
    public function save(InsuranceCompany $model)
    {
        if ($model->save() === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param InsuranceCompany $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(InsuranceCompany $model)
    {
        if ($model->delete() === false) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}