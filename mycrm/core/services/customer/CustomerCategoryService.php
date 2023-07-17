<?php

namespace core\services\customer;

use core\models\customer\CustomerCategory;
use core\repositories\customer\CustomerCategoryRepository;
use core\services\TransactionManager;

class CustomerCategoryService
{
    private $transactionManager;
    /**
     * @var CustomerCategoryRepository
     */
    private $customerCategories;

    public function __construct(
        CustomerCategoryRepository $customerCategories,
        TransactionManager $transactionManager
    ) {
        $this->transactionManager = $transactionManager;
        $this->customerCategories = $customerCategories;
    }

    /**
     * @param string $name
     * @param int $company_id
     * @return CustomerCategory
     */
    public function create(string $name, int $company_id, $discount, $color)
    {
        $model = CustomerCategory::add($name, $company_id, $discount, $color);

        $this->transactionManager->execute(function () use ($model) {
            $this->customerCategories->add($model);
        });

        return $model;
    }

    /**
     * @param int $id
     * @param string $name
     * @return CustomerCategory
     */
    public function update(int $id, string $name, $discount, $color)
    {
        $model = $this->customerCategories->find($id);
        $this->guardOwnCategory($model);
        $model->edit($name, $discount, $color);

        $this->transactionManager->execute(function () use ($model) {
            $this->customerCategories->edit($model);
        });

        return $model;
    }


    /**
     * @param CustomerCategory $model
     */
    public function guardOwnCategory(CustomerCategory $model)
    {
        if ($model->company_id !== \Yii::$app->user->identity->company_id) {
            throw new \DomainException("Вы не можете редактировать категорию другой компании.");
        }
    }
}