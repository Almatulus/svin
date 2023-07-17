<?php

namespace core\services\warehouse;

use core\forms\warehouse\product\ProductCreateForm;
use core\forms\warehouse\product\ProductUpdateForm;
use core\models\warehouse\Product;
use core\models\warehouse\ProductType;
use core\repositories\company\CompanyRepository;
use core\repositories\division\DivisionServiceProductRepository;
use core\repositories\warehouse\ProductRepository;
use core\services\TransactionManager;

class ProductService
{
    private $divisionServiceProductRepository;
    private $productRepository;
    private $transactionManager;
    private $companyRepository;

    public function __construct(
        TransactionManager $transactionManager,
        DivisionServiceProductRepository $divisionServiceProductRepository,
        ProductRepository $productRepository,
        CompanyRepository $companyRepository
    ) {
        $this->divisionServiceProductRepository = $divisionServiceProductRepository;
        $this->productRepository = $productRepository;
        $this->companyRepository = $companyRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param ProductCreateForm $form
     * @return Product
     * @throws \Exception
     */
    public function create(ProductCreateForm $form): Product
    {
        $product = Product::create(
            $form->barcode,
            $form->description,
            $form->category_id,
            $form->division_id,
            $form->manufacturer_id,
            $form->min_quantity,
            $form->name,
            $form->quantity,
            $form->price,
            $form->purchase_price,
            $form->sku,
            $form->vat,
            $form->unit_id
        );

        $types = $this->getTypes(...$form->types);

        $this->transactionManager->execute(function () use ($product, $types) {
            $this->productRepository->add($product);
            foreach ($types as $productType) {
                $product->link('productTypes', $productType);
            }
        });

        return $product;
    }

    /**
     * @param int[] ...$typeIds
     * @return ProductType[]
     */
    protected function getTypes(int ...$typeIds)
    {
        return array_map(function (int $typeId) {
            return $this->productRepository->findType($typeId);
        }, $typeIds);
    }

    /**
     * @param int $id
     * @param ProductUpdateForm $form
     * @return Product
     * @throws \Exception
     */
    public function update(int $id, ProductUpdateForm $form): Product
    {
        $product = $this->productRepository->find($id);

        $product->edit(
            $form->barcode,
            $form->description,
            $form->category_id,
            $form->division_id,
            $form->manufacturer_id,
            $form->min_quantity,
            $form->name,
            $form->quantity,
            $form->price,
            $form->purchase_price,
            $form->sku,
            $form->vat,
            $form->unit_id
        );

        $types = $this->getTypes(...$form->types);

        $this->transactionManager->execute(function () use ($product, $types) {
            $this->productRepository->edit($product);
            $product->unlinkAll('productTypes', true);
            foreach ($types as $productType) {
                $product->link('productTypes', $productType);
            }
        });

        return $product;
    }

    /**
     * TODO need protection from deleting other companies' products?
     * @param int $id
     * @throws \Exception
     */
    public function remove(int $id)
    {
        $product = $this->productRepository->find($id);
        $product->remove();

        $this->transactionManager->execute(function () use ($product) {
            $this->productRepository->edit($product);
            $this->divisionServiceProductRepository->batchDeleteByProductId($product->id);
        });
    }

    /**
     * @param $ids
     * @throws \Exception
     */
    public function batchRemove($ids)
    {
        foreach ($ids as $id){
            $this->remove($id);
        }
    }

    /**
     * TODO need protection from deleting other companies' products?
     * TODO when restoring, cannot restore divisionServiceProducts (already hard deleted from DB)
     * @param int $id
     * @throws \Exception
     */
    public function restore(int $id)
    {
        $product = $this->productRepository->find($id);
        $product->restore();

        $this->transactionManager->execute(function () use ($product) {
            $this->productRepository->edit($product);
        });
    }
}