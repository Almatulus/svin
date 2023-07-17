<?php

namespace core\repositories\company;

use core\models\company\Referrer;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class ReferrerRepository extends BaseRepository
{
    /**
     * @param $id
     *
     * @return Referrer
     * @throws NotFoundException
     */
    public function find($id)
    {
        if ( ! $model = Referrer::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }

        return $model;
    }
}