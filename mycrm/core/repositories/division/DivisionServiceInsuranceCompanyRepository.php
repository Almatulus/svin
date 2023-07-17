<?php

namespace core\repositories\division;

use core\models\division\DivisionServiceInsuranceCompany;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class DivisionServiceInsuranceCompanyRepository extends BaseRepository
{
    /**
     * @param $id
     * @return DivisionServiceInsuranceCompany
     */
    public function find($id)
    {
        if (!$model = DivisionServiceInsuranceCompany::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param array $ids
     * @return int
     */
    public function batchDelete(array $ids)
    {
        return DivisionServiceInsuranceCompany::deleteAll(['id' => $ids]);
    }
}