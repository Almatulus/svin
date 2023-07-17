<?php

namespace core\repositories;

use core\models\webcall\WebCall;
use core\models\webcall\WebcallAccount;
use core\repositories\exceptions\NotFoundException;

class WebcallRepository extends BaseRepository
{
    /**
     * @param $id
     * @return Webcall
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = Webcall::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $company_id
     * @return WebCall
     */
    public function findByCompany($company_id)
    {
        if (!$model = Webcall::findOne(['company_id' => $company_id])) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $id
     * @return WebcallAccount
     */
    public function findAccount($id)
    {
        if (!$model = WebcallAccount::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }
}