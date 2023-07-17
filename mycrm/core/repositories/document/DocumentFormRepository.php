<?php

namespace core\repositories\document;

use core\models\document\DocumentForm;
use core\repositories\exceptions\NotFoundException;

class DocumentFormRepository
{
    /**
     * @param $id
     * @return DocumentForm
     */
    public function find($id)
    {
        if (!$model = DocumentForm::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    public function add(DocumentForm $model)
    {
        if (!$model->getIsNewRecord()) {
            throw new \RuntimeException('Adding existing model.');
        }
        if (!$model->insert(false)) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function edit(DocumentForm $model)
    {
        if ($model->getIsNewRecord()) {
            throw new \RuntimeException('Saving new model.');
        }
        if ($model->update(false) === false) {
            throw new \RuntimeException('Saving error.');
        }
    }

    public function delete(DocumentForm $model)
    {
        if (!$model->delete()) {
            throw new \RuntimeException('Deleting error.');
        }
    }
}