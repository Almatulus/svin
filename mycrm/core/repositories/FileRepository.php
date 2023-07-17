<?php

namespace core\repositories;

use core\models\File;
use core\repositories\exceptions\NotFoundException;

class FileRepository extends BaseRepository
{
    /**
     * @param $id
     * @return File
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = File::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $key
     * @return File
     * @throws NotFoundException
     */
    public function findByPath($path)
    {
        if (!$model = File::find()->where(['path' => $path])->one()) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }
}