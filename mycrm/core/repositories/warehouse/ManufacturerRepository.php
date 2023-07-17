<?php

namespace core\repositories\warehouse;

use core\models\warehouse\Manufacturer;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class ManufacturerRepository extends BaseRepository
{
    /**
     * @param $id
     * @return Manufacturer
     */
    public function find($id)
    {
        if (!$model = Manufacturer::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

}