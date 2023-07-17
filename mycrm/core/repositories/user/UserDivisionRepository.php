<?php

namespace core\repositories\user;

use core\models\Staff;
use core\models\user\UserDivision;
use core\repositories\exceptions\NotFoundException;

class UserDivisionRepository
{
    /**
     * @param $id
     *
     * @return UserDivision
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = UserDivision::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param UserDivision $model
     */
    public function save(UserDivision $model)
    {
        if ( ! $model->insert()) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function clearStaffDivisions($staff_id)
    {
        UserDivision::deleteAll(['staff_id' => $staff_id]);
    }

    /**
     * @param UserDivision $model
     */
    public function delete(UserDivision $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }

    public function clearStaff(Staff $staff)
    {
        if ($staff->isNewRecord) {
            throw new \RuntimeException('Deleting error.');
        }
        UserDivision::deleteAll(['staff_id' => $staff->id]);
    }
}
