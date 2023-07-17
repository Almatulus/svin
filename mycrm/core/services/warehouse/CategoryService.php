<?php

namespace core\services\warehouse;

use core\models\warehouse\Category;
use core\repositories\warehouse\CategoryRepository;
use core\services\TransactionManager;

class CategoryService
{
    /**
     * @var CategoryRepository
     */
    private $categories;

    /**
     * @var TransactionManager
     */
    protected $transactionManager;

    public function __construct(
        CategoryRepository $categoryRepository,
        TransactionManager $transactionManager
    )
    {
        $this->categories = $categoryRepository;
        $this->transactionManager = $transactionManager;
    }


    public function create($name, $company_id)
    {
        $category = Category::create($name, $company_id);

        $this->transactionManager->execute(function () use ($category) {
            $this->categories->add($category);
        });

        return $category;
    }

    public function edit($id, $name)
    {
        $category = $this->categories->find($id);
        $category->edit($name);

        $this->transactionManager->execute(function () use ($category) {
            $this->categories->edit($category);
        });

        return $category;
    }

}
