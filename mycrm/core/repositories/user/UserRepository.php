<?php

namespace core\repositories\user;

use core\models\user\User;
use core\repositories\exceptions\NotFoundException;

class UserRepository
{
    /**
     * @param $id
     *
     * @return User
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = User::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param string $username
     *
     * @return User
     * @throws NotFoundException
     */
    public function findByUsername($username)
    {
        if ( ! $model = User::findByUsername($username)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param string $phone
     *
     * @return User
     * @throws NotFoundException
     */
    public function findByPhone($phone)
    {
        if (!$model = User::find()->where(['username' => $phone])->one()) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param integer $company_id
     *
     * @return User[]
     * @throws NotFoundException
     */
    public function findAllByCompany($company_id)
    {
        return User::findAll(['company_id' => $company_id]);
    }

    /**
     * @param User $model
     */
    public function add(User $model)
    {
        if ( ! $model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if ( ! $model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param User $model
     */
    public function edit(User $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param User $model
     */
    public function delete(User $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}