<?php

namespace core\repositories\customer;

use core\models\customer\CustomerRequestTemplate;
use core\repositories\BaseRepository;
use core\repositories\exceptions\NotFoundException;

class CustomerRequestTemplateRepository extends BaseRepository
{
    /**
     * @param $id
     * @return CustomerRequestTemplate
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = CustomerRequestTemplate::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $type
     * @return CustomerRequestTemplate[]
     */
    public function findByType($type)
    {
        return CustomerRequestTemplate::find()->where(['key' => $type, 'is_enabled' => true])->all();
    }
}