<?php

namespace core\services\company;

use core\models\company\CompanyPosition;
use core\repositories\company\CompanyPositionRepository;
use core\repositories\company\InsuranceRepository;
use core\repositories\document\DocumentFormRepository;
use core\repositories\medCard\MedCardCommentRepository;
use core\repositories\company\CompanyRepository;
use core\services\TransactionManager;

/**
 * @var InsuranceRepository insuranceRepository
 */
class CompanyPositionService
{
    private $transactionManager;
    private $companyRepository;
    private $medCardCommentRepository;
    private $companyPositionRepository;
    private $documentFormRepository;

    /**
     * CompanyPositionService constructor.
     *
     * @param CompanyPositionRepository $companyPositionRepository
     * @param CompanyRepository         $companyRepository
     * @param MedCardCommentRepository  $medCardCommentRepository
     * @param DocumentFormRepository    $documentFormRepository
     * @param TransactionManager        $transactionManager
     */
    public function __construct(
        CompanyPositionRepository $companyPositionRepository,
        CompanyRepository $companyRepository,
        MedCardCommentRepository $medCardCommentRepository,
        DocumentFormRepository $documentFormRepository,
        TransactionManager $transactionManager
    ) {
        $this->transactionManager        = $transactionManager;
        $this->companyRepository         = $companyRepository;
        $this->medCardCommentRepository  = $medCardCommentRepository;
        $this->companyPositionRepository = $companyPositionRepository;
        $this->documentFormRepository    = $documentFormRepository;
    }

    /**
     * @param integer $company_id
     * @param integer $id
     *
     * @return CompanyPosition
     */
    public function find($company_id, $id)
    {
        return $this->companyPositionRepository->find($id, $company_id);
    }

    /**
     * @param integer $company_id
     * @param string  $name
     * @param string  $description
     * @param array   $category_ids
     * @param array   $document_form_ids
     *
     * @return CompanyPosition
     */
    public function add($company_id, $name, $description, $category_ids, $document_form_ids)
    {
        $categories = [];
        if (!empty($category_ids)) {
            $categories = array_map(function ($category_id) {
                return $this->medCardCommentRepository->find($category_id);
            }, $category_ids);
        }
        $documentForms = [];
        if (!empty($document_form_ids)) {
            $documentForms = array_map(function ($document_form_id) {
                return $this->documentFormRepository->find($document_form_id);
            }, $document_form_ids);
        }
        $company = $this->companyRepository->find($company_id);

        $model = CompanyPosition::add(
            $name,
            $description,
            $company
        );
        $model->setCategories($categories);
        $model->setDocumentFormsRelation($documentForms);

        $this->transactionManager->execute(function () use ($model) {
            $this->companyPositionRepository->add($model);
        });

        return $model;
    }

    /**
     * @param integer $company_id
     * @param integer $company_position_id
     * @param string  $name
     * @param string  $description
     * @param array   $category_ids
     * @param array   $document_form_ids
     *
     * @return CompanyPosition
     */
    public function edit($company_id, $company_position_id, $name, $description, $category_ids, $document_form_ids)
    {
        $model = $this->companyPositionRepository->find($company_position_id, $company_id);

        $categories = [];
        if (!empty($category_ids)) {
            $categories = array_map(function ($category_id) {
                return $this->medCardCommentRepository->find($category_id);
            }, $category_ids);
        }
        $documentForms = [];
        if (!empty($document_form_ids)) {
            $documentForms = array_map(function ($document_form_id) {
                return $this->documentFormRepository->find($document_form_id);
            }, $document_form_ids);
        }

        $model->setCategories($categories);
        $model->setDocumentFormsRelation($documentForms);
        $model->edit($name, $description);

        $this->transactionManager->execute(function () use ($model) {
            $this->companyPositionRepository->edit($model);
        });

        return $model;
    }

    /**
     * @param integer $company_id
     * @param integer $company_position_id
     *
     * @return CompanyPosition
     */
    public function delete($company_id, $company_position_id)
    {
        $model = $this->companyPositionRepository->find($company_position_id, $company_id);

        $this->transactionManager->execute(function () use ($model) {
            $this->companyPositionRepository->delete($model);
        });

        return $model;
    }

    /**
     * @param integer $company_id
     * @param array $ids
     */
    public function deleteMultiple($company_id, $ids)
    {
        $models = [];
        foreach ($ids as $company_position_id) {
            $models[] = $this->companyPositionRepository->find($company_position_id, $company_id);
        }

        $this->transactionManager->execute(function () use ($models) {
            foreach ($models as $model) {
                $this->companyPositionRepository->delete($model);
            }
        });
    }
}
