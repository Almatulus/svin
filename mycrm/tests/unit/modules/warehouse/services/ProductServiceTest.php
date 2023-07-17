<?php

namespace modules\warehouse\services;


use core\forms\warehouse\product\ProductCreateForm;
use core\forms\warehouse\product\ProductUpdateForm;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\division\DivisionServiceProduct;
use core\models\warehouse\Category;
use core\models\warehouse\Manufacturer;
use core\models\warehouse\Product;
use core\models\warehouse\ProductType;
use core\models\warehouse\ProductUnit;
use core\services\warehouse\ProductService;

class ProductServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ProductService
     */
    private $service;

    /**
     * @var Category
     */
    private $category;
    /**
     * @var Division
     */
    private $division;
    /**
     * @var Manufacturer
     */
    private $manufacturer;
    /**
     * @var ProductType[]
     */
    private $types;
    /**
     * @var ProductUnit
     */
    private $unit;

    public function testCreate()
    {
        $form = new ProductCreateForm([
            'barcode'         => $this->tester->getFaker()->text(),
            'description'     => $this->tester->getFaker()->text(),
            'category_id'     => $this->category->id,
            'division_id'     => $this->division->id,
            'manufacturer_id' => $this->manufacturer->id,
            'min_quantity'    => $this->tester->getFaker()->randomNumber(2),
            'name'            => $this->tester->getFaker()->name,
            'quantity'        => $this->tester->getFaker()->randomNumber(2),
            'price'           => $this->tester->getFaker()->randomNumber(3),
            'purchase_price'  => $this->tester->getFaker()->randomNumber(3),
            'sku'             => $this->tester->getFaker()->text(16),
            'types'           => [
                $this->types[0]->id,
                $this->types[1]->id,
            ],
            'vat'             => rand(0, 50),
            'unit_id'         => $this->unit->id
        ]);

        $model = $this->service->create($form);

        $mapId = function ($item) {
            return $item->id;
        };
        $attribute_keys = array_diff(array_keys($form->getAttributes()), ["types"]);

        verify($model)->isInstanceOf(Product::class);
        verify($model->id)->notNull();
        verify($model->getAttributes($attribute_keys))->equals($form->getAttributes($attribute_keys));
        verify(array_map($mapId, $model->productTypes))->equals($form->types);
    }

    public function testUpdate()
    {
        $product = $this->tester->getFactory()->create(Product::class);

        $form = new ProductUpdateForm($product->id);
        $form->setAttributes([
            'barcode'         => $this->tester->getFaker()->text(),
            'description'     => $this->tester->getFaker()->text(),
            'category_id'     => $this->category->id,
            'division_id'     => $this->division->id,
            'manufacturer_id' => $this->manufacturer->id,
            'min_quantity'    => $this->tester->getFaker()->randomNumber(2),
            'name'            => $this->tester->getFaker()->name,
            'quantity'        => $this->tester->getFaker()->randomNumber(2),
            'price'           => $this->tester->getFaker()->randomNumber(3),
            'purchase_price'  => $this->tester->getFaker()->randomNumber(3),
            'sku'             => $this->tester->getFaker()->text(16),
            'types'           => [
                $this->types[0]->id,
                $this->types[1]->id,
            ],
            'vat'             => rand(0, 50),
            'unit_id'         => $this->unit->id
        ]);

        $model = $this->service->update($product->id, $form);

        $mapId = function ($item) {
            return $item->id;
        };
        $attribute_keys = array_diff(array_keys($form->getAttributes()), ["types"]);

        verify($model)->isInstanceOf(Product::class);
        verify($model->id)->notNull();
        verify($model->getAttributes($attribute_keys))->equals($form->getAttributes($attribute_keys));
        verify(array_map($mapId, $model->productTypes))->equals($form->types);
    }

    public function testRemove()
    {
        $product = $this->tester->getFactory()->create(Product::class);
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $divisionService->link('divisions', $this->division);

        $this->tester->getFactory()->create(DivisionServiceProduct::class, [
            'division_service_id' => $divisionService->id,
            'product_id'          => $product->id
        ]);

        $this->service->remove($product->id);

        $this->tester->canSeeRecord(Product::class, [
            'id'     => $product->id,
            'status' => Product::STATUS_DISABLED
        ]);
        $this->tester->cantSeeRecord(DivisionServiceProduct::class, ['product_id' => $product->id]);
    }

    public function testRestore()
    {
        $product = $this->tester->getFactory()->create(Product::class, [
            'status' => Product::STATUS_DISABLED
        ]);

        $this->tester->cantSeeRecord(Product::class, [
            'id'     => $product->id,
            'status' => Product::STATUS_ENABLED
        ]);

        $this->service->restore($product->id);

        $this->tester->canSeeRecord(Product::class, [
            'id'     => $product->id,
            'status' => Product::STATUS_ENABLED
        ]);
    }

    protected function _before()
    {
        $this->service = \Yii::createObject(ProductService::class);

        $this->category = $this->tester->getFactory()->create(Category::class);
        $this->division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->category->company_id
        ]);
        $this->manufacturer = $this->tester->getFactory()->create(Manufacturer::class, [
            'company_id' => $this->category->company_id
        ]);
        $this->types = [
            $this->tester->getFactory()->create(ProductType::class),
            $this->tester->getFactory()->create(ProductType::class)
        ];
        $this->unit = $this->tester->getFactory()->create(ProductUnit::class);
    }

    protected function _after()
    {

    }

}