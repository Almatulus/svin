<?php

namespace core\repositories;

use core\models\Staff;
use core\repositories\exceptions\NotFoundException;

class StaffRepository
{
    /**
     * @param $id
     * @return Staff
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = Staff::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $user_id
     * @return Staff
     * @throws NotFoundException
     */
    public function findByUser($user_id)
    {
        if (!$model = Staff::findOne(['user_id' => $user_id])) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param Staff $model
     */
    public function save(Staff $model)
    {
        if (!$model->save(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(Staff $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}