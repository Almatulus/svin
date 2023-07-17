<?php

namespace core\repositories\company;

use core\models\company\CompanyPosition;
use core\models\company\query\CompanyPositionQuery;
use core\repositories\exceptions\NotFoundException;

class CompanyPositionRepository
{
    /**
     * @param $id
     * @param $company_id
     *
     * @return CompanyPosition
     * @throws NotFoundException
     */
    public function find($id, $company_id = null)
    {
        /** @var CompanyPositionQuery $query */
        $query = CompanyPosition::find()
            ->notDeleted()
            ->position($id);

        if($company_id !== null) {
            $query->company($company_id);
        }

        if (($model = $query->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundException('Model not found.');
        }
    }

    public function add(CompanyPosition $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(CompanyPosition $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(CompanyPosition $model)
    {
        if (!$model->softDelete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}