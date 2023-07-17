<?php

namespace core\services;

use core\models\finance\CompanyCostItem;
use core\repositories\CompanyCostItemRepository;
use core\repositories\company\CompanyRepository;

class CompanyCostItemService
{
    private $companyRepository;
    private $companyCostItemRepository;
    private $transactionManager;

    /**
     * CompanyCashService constructor.
     *
     * @param CompanyRepository         $companyRepository
     * @param CompanyCostItemRepository $companyCostItemRepository
     * @param TransactionManager        $transactionManager
     */
    public function __construct(
        CompanyRepository $companyRepository,
        CompanyCostItemRepository $companyCostItemRepository,
        TransactionManager $transactionManager
    ) {
        $this->companyRepository         = $companyRepository;
        $this->companyCostItemRepository = $companyCostItemRepository;
        $this->transactionManager        = $transactionManager;
    }

    /**
     * @param $comments
     * @param $company_id
     * @param $name
     * @param $type
     * @param $divisions
     * @param $category_id
     *
     * @return CompanyCostItem
     * @throws \Exception
     */
    public function add($comments, $company_id, $name, $type, $divisions, $category_id)
    {
        $company = $this->companyRepository->find($company_id);

        $companyCostItem = CompanyCostItem::add(
            $company,
            $name,
            $type,
            $comments,
            null,
            true,
            $category_id
        );

        $this->transactionManager->execute(function () use (
            $companyCostItem,
            $divisions
        ) {
            $this->companyCostItemRepository->add($companyCostItem);
            $this->linkDivisions($divisions, $companyCostItem->id);
        });

        return $companyCostItem;
    }

    /**
     * @param $id
     * @param $comments
     * @param $company_id
     * @param $name
     * @param $type
     * @param $divisions
     * @param $category_id
     *
     * @return CompanyCostItem
     * @throws \Exception
     */
    public function edit($id, $comments, $company_id, $name, $type, $divisions, $category_id)
    {
        $companyCostItem = $this->companyCostItemRepository->find($id);
        $company         = $this->companyRepository->find($company_id);

        $companyCostItem->edit(
            $company,
            $name,
            $type,
            $comments,
            $category_id
        );

        $this->transactionManager->execute(function () use (
            $companyCostItem,
            $divisions
        ) {
            $this->companyCostItemRepository->edit($companyCostItem);
            $this->companyCostItemRepository->unlinkAllDivisions($companyCostItem->id);
            $this->linkDivisions($divisions, $companyCostItem->id);
        });

        return $companyCostItem;
    }

    /**
     * Link with divisions
     *
     * @param array $divisions
     * @param       $id
     */
    final protected function linkDivisions($divisions, $id)
    {
        if (empty($divisions)) {
            return;
        }

        foreach ($divisions as $division_id) {
            $this->companyCostItemRepository->linkDivision($id, $division_id);
        }
    }
}
