<?php

namespace core\repositories\division;

use core\models\division\DivisionPhone;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class DivisionPhoneRepository extends BaseRepository
{
    /**
     * @param $id
     * @return DivisionPhone
     */
    public function find($id)
    {
        if (!$model = DivisionPhone::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $division_id
     * @param $phone
     * @return DivisionPhone
     */
    public function findByPhone($division_id, $phone)
    {
        $model = DivisionPhone::findOne([
            'division_id' => $division_id,
            'value' => $phone
        ]);
        if (!$model) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $division_id
     * @return int
     */
    public function deletePhones($division_id)
    {
        return DivisionPhone::updateAll(
            ['status' => DivisionPhone::STATUS_DISABLED],
            ['division_id' => $division_id]
        );
    }
}