<?php

namespace modules\warehouse\services;

use core\models\company\Company;
use core\models\division\Division;
use core\models\user\User;
use core\models\warehouse\Category;
use core\models\warehouse\Product;
use core\models\warehouse\Stocktake;
use core\models\warehouse\StocktakeProduct;
use core\repositories\exceptions\InsufficientStockLevel;
use core\services\warehouse\StocktakeService;

class StocktakeServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var StocktakeService
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
     * @var User
     */
    private $user;

    /**
     * @var Company
     */
    private $company;

    public function testCreate()
    {
        $this->tester->getFactory()->seed(5, Product::class,[
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'company_id'  => $this->company->id
        ]);

        $model = $this->service->create(
            null,
            $this->category->id,
            $this->tester->getFaker()->name,
            $this->division->id,
            $this->user->id,
            $this->tester->getFaker()->text
        );

        verify($model)->isInstanceOf(Stocktake::class);
        verify($model->id)->notNull();
        verify($model->status)->equals(Stocktake::STATUS_NEW);

        $this->tester->amGoingTo("Test that service won't create new stocktake, if not finished exists");
        $this->expectException(\DomainException::class);

        $this->service->create(
            null,
            $this->category->id,
            $this->tester->getFaker()->name,
            $this->division->id,
            $this->company->id,
            $this->user->id,
            $this->tester->getFaker()->text
        );
    }

    public function testCreateWithoutProducts()
    {
        $this->expectException(InsufficientStockLevel::class);

        $this->service->create(
            null,
            $this->category->id,
            $this->tester->getFaker()->name,
            $this->division->id,
            $this->company->id,
            $this->user->id,
            $this->tester->getFaker()->text
        );
    }

    public function testEditProducts()
    {
        $this->tester->getFactory()->seed(5, Product::class,[
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'company_id'  => $this->company->id
        ]);

        $stocktake = $this->tester->getFactory()->create(Stocktake::class, [
            'category_id' => $this->category->id,
            'division_id' => $this->division->id,
            'type_of_products' => null,
            'status' => Stocktake::STATUS_NEW
        ]);

        $stocktakeProducts = $this->service->getProducts($stocktake->id);

        foreach($stocktakeProducts as $stocktakeProduct){
            $stocktakeProduct->actual_stock_level = $stocktakeProduct->product->quantity + 1;
        }

        $existingStockTakeProduct = $this->tester->getFactory()->create(StocktakeProduct::class, [
            'stocktake_id' => $stocktake->id
        ]);

        $this->tester->canSeeRecord(StocktakeProduct::class,[
            'id' => $existingStockTakeProduct->id
        ]);

        $stocktake = $this->service->editProducts($stocktake->id, $stocktakeProducts);

        $this->tester->amGoingTo("check if new stocktakeProducts were added");
        foreach($stocktakeProducts as $stocktakeProduct){
            $this->tester->canSeeRecord(StocktakeProduct::class,[
                'stocktake_id' => $stocktake->id
            ]);
        }

        $this->tester->amGoingTo("check if old stocktakeProduct was deleted");
        $this->tester->cantSeeRecord(StocktakeProduct::class,[
            'id' => $existingStockTakeProduct->id
        ]);

        verify($stocktake->status)->equals(Stocktake::STATUS_CORRECTED);
    }

    public function testUpdateProductsQuantity()
    {
        $stocktake = $this->tester->getFactory()->create(Stocktake::class, [
            'category_id' => $this->category->id,
            'division_id' => $this->division->id,
            'type_of_products' => null
        ]);

        /**
         * @var StocktakeProduct[] $stocktakeProducts
         */
        $stocktakeProducts = $this->tester->getFactory()->seed(5, StocktakeProduct::class, [
            'stocktake_id' => $stocktake->id
        ]);

        $stocktake = $this->service->updateProductsQuantity($stocktake->id, $stocktakeProducts);

        foreach ($stocktakeProducts as $stocktakeProduct){
            verify($stocktakeProduct->actual_stock_level)->equals($stocktakeProduct->product->quantity);
        }
        verify($stocktake->status)->equals(Stocktake::STATUS_COMPLETED);
    }

    public function testComplete()
    {
        $stocktake = $this->tester->getFactory()->create(Stocktake::class, [
            'category_id' => $this->category->id,
            'division_id' => $this->division->id,
            'type_of_products' => null
        ]);

        $stocktake = $this->service->complete($stocktake->id);

        verify($stocktake->status)->equals(Stocktake::STATUS_COMPLETED);
    }

    public function testGetProductsWithoutOldModels()
    {
        $stocktake = $this->tester->getFactory()->create(Stocktake::class, [
            'category_id' => $this->category->id,
            'division_id' => $this->division->id,
            'type_of_products' => null
        ]);

        $productsCount = rand(1, 10);
        $this->tester->getFactory()->seed($productsCount, Product::class,[
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'company_id'  => $this->company->id
        ]);

        $stocktakeProducts = $this->service->getProducts($stocktake->id);

        $this->tester->amGoingTo("Check that service created StocktakeProducts from existing products
                                  and their count is equal");
        verify($productsCount)->equals(count($stocktakeProducts));
    }

    public function testGetProductsWithOldModels()
    {
        $stocktake = $this->tester->getFactory()->create(Stocktake::class, [
            'category_id' => $this->category->id,
            'division_id' => $this->division->id,
            'type_of_products' => null
        ]);

        $stocktakeProductsCount = rand(1, 10);
        $productsCount = rand(11, 20);

        $this->tester->getFactory()->seed($stocktakeProductsCount, StocktakeProduct::class, [
            'stocktake_id' => $stocktake->id
        ]);

        $this->tester->getFactory()->seed($productsCount, Product::class,[
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'company_id'  => $this->company->id
        ]);

        $stocktakeProducts = $this->service->getProducts($stocktake->id);

        $this->tester->amGoingTo("Check that service used existing StocketakeProducts and their count is equal");
        verify($stocktakeProductsCount)->equals(count($stocktakeProducts));
    }

    public function testGetCurrent()
    {
        verify($this->service->getCurrent())->equals(false);

        $this->tester->getFactory()->create(Stocktake::class, [
            'category_id' => $this->category->id,
            'division_id' => $this->division->id,
            'company_id' => $this->company->id,
            'type_of_products' => null
        ]);

        verify($this->service->getCurrent())->isInstanceOf(Stocktake::class);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function _before()
    {
        $this->service = \Yii::createObject(StocktakeService::class);

        $this->user = $this->tester->login();
        $this->company = $this->user->company;

        $this->category = $this->tester->getFactory()->create(Category::class,[
            'company_id' => $this->company->id
        ]);

        $this->division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->company->id
        ]);
    }

    protected function _after()
    {

    }

}