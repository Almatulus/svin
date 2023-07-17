<?php

namespace core\repositories;

use core\models\finance\CompanyCash;
use core\repositories\exceptions\NotFoundException;

class CompanyCashRepository extends BaseRepository
{
    /**
     * @param $id
     * @return CompanyCash
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = CompanyCash::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * Returns first payment model sorted by id
     * @param integer $company_id
     * @return CompanyCash
     */
    public function findFirst($company_id)
    {
        /* @var CompanyCash $model */
        if (!$model = CompanyCash::find()->where(['company_id' => $company_id])->orderBy('id')->one()) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * Returns first payment model sorted by id
     * @param integer $division_id
     * @return CompanyCash
     */
    public function findFirstByDivision($division_id)
    {
        /* @var CompanyCash $model */
        if (!$model = CompanyCash::find()->where(['division_id' => $division_id])->orderBy('id')->one()) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }
}