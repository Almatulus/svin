<?php

namespace core\repositories;

use core\models\ConfirmKey;
use core\repositories\exceptions\NotFoundException;

class ConfirmKeyRepository
{
    /**
     * @param $id
     *
     * @return ConfirmKey
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = ConfirmKey::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param $code
     * @param $username
     *
     * @return ConfirmKey
     */
    public function findActiveByCodeAndUsername($code, $username)
    {
        /* @var ConfirmKey $model */
        $model = ConfirmKey::find()
                           ->where([
                               'username' => $username,
                               'status'   => ConfirmKey::STATUS_ENABLED,
                               'code'     => $code
                           ])
                           ->andWhere([
                               '>=',
                               'expired_at',
                               date('Y-m-d H:i:s')
                           ])
                           ->one();

        if ( ! $model) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }

    /**
     * @param ConfirmKey $model
     *
     * @throws \Exception|\Throwable
     */
    public function save(ConfirmKey $model)
    {
        if ( ! $model->save()) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @param ConfirmKey $model
     *
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(ConfirmKey $model)
    {
        if ( ! $model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}