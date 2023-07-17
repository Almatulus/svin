<?php

namespace core\repositories\rbac;

use core\models\rbac\AuthAssignment;
use core\repositories\exceptions\NotFoundException;

class AuthAssignmentRepository
{
    /**
     * @param $id
     *
     * @return AuthAssignment
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = AuthAssignment::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    public function save(AuthAssignment $model)
    {
        if ( ! $model->save(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function clearPermissions(int $user_id)
    {
        AuthAssignment::deleteAll(['user_id' => $user_id]);
    }

    public function delete(AuthAssignment $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}