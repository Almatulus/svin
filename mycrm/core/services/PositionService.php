<?php

namespace core\services;

use core\models\Position;
use core\repositories\company\InsuranceRepository;
use core\repositories\document\DocumentFormRepository;
use core\repositories\PositionRepository;

/**
 * @var InsuranceRepository insuranceRepository
 */
class PositionService
{
    private $transactionManager;
    private $positionRepository;
    private $documentFormRepository;

    /**
     * PositionService constructor.
     *
     * @param PositionRepository $positionRepository
     * @param DocumentFormRepository    $documentFormRepository
     * @param TransactionManager        $transactionManager
     */
    public function __construct(
        PositionRepository $positionRepository,
        DocumentFormRepository $documentFormRepository,
        TransactionManager $transactionManager
    ) {
        $this->transactionManager        = $transactionManager;
        $this->positionRepository        = $positionRepository;
        $this->documentFormRepository    = $documentFormRepository;
    }

    /**
     * @param integer $id
     *
     * @return Position
     */
    public function find($id)
    {
        return $this->positionRepository->find($id);
    }

    /**
     * @param string  $name
     * @param string  $description
     * @param array   $document_form_ids
     *
     * @return Position
     */
    public function add($name, $description, $document_form_ids)
    {

        $documentForms = [];
        if (!empty($document_form_ids)) {
            $documentForms = array_map(function ($document_form_id) {
                return $this->documentFormRepository->find($document_form_id);
            }, $document_form_ids);
        }

        $model = Position::add($name, $description);
        $model->setDocumentFormsRelation($documentForms);

        $this->transactionManager->execute(function () use ($model) {
            $this->positionRepository->add($model);
        });

        return $model;
    }

    /**
     * @param integer $position_id
     * @param string  $name
     * @param string  $description
     * @param array   $document_form_ids
     *
     * @return Position
     */
    public function edit($position_id, $name, $description, $document_form_ids)
    {
        $model = $this->positionRepository->find($position_id);

        $documentForms = [];
        if ( ! empty($document_form_ids) ) {
            $documentForms = array_map(function ($document_form_id) {
                return $this->documentFormRepository->find($document_form_id);
            }, $document_form_ids);
        }

        $model->setDocumentFormsRelation($documentForms);
        $model->edit($name, $description);

        $this->transactionManager->execute(function () use ($model) {
            $this->positionRepository->edit($model);
        });

        return $model;
    }

    /**
     * @param integer $position_id
     *
     * @return Position
     */
    public function delete($position_id)
    {
        $model = $this->positionRepository->find($position_id);

        $this->transactionManager->execute(function () use ($model) {
            $this->positionRepository->delete($model);
        });

        return $model;
    }

    /**
     * @param array $ids
     */
    public function deleteMultiple($ids)
    {
        $models = [];
        foreach ($ids as $position_id) {
            $models[] = $this->positionRepository->find($position_id);
        }

        $this->transactionManager->execute(function () use ($models) {
            foreach ($models as $model) {
                $this->positionRepository->delete($model);
            }
        });
    }
}
