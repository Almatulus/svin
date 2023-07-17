<?php

namespace core\forms\warehouse\product;

use core\models\warehouse\Product;
use core\models\warehouse\ProductType;

class ProductUpdateForm extends ProductCreateForm
{
    /**
     * ProductUpdateForm constructor.
     * @param int $id
     * @param array $config
     */
    public function __construct(int $id, array $config = [])
    {
        parent::__construct($config);

        $this->product = Product::findOne($id);

        if ($this->product) {
            $this->attributes = $this->product->attributes;
            $this->types = array_map(function (ProductType $type) {
                return $type->id;
            }, $this->product->productTypes);
        }
    }
}