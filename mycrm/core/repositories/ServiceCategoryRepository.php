<?php

namespace core\repositories;

use core\models\ServiceCategory;
use core\repositories\exceptions\NotFoundException;

class ServiceCategoryRepository extends BaseRepository
{
    /**
     * @param $id
     * @return ServiceCategory
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = ServiceCategory::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $company_id
     * @param string $name
     * @return ServiceCategory
     */
    public function findByCompanyAndName($company_id, $name)
    {
        /* @var ServiceCategory $model */
        $model = ServiceCategory::find()->where([
            'company_id' => $company_id,
            'name' => $name
        ])->orderBy('id')->one();
        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }
}