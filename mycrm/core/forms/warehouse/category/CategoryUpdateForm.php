<?php

namespace core\forms\warehouse\category;

use core\models\warehouse\Category;

class CategoryUpdateForm extends CategoryCreateForm
{
    /**
     * CategoryUpdateForm constructor.
     * @param Category $category
     * @param array $config
     * @internal param int $id
     */
    public function __construct(Category $category, array $config = [])
    {
        parent::__construct($config);

        $this->name = $category->name;
    }
}