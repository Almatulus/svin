<?php

namespace services;

use core\models\division\Division;
use core\models\warehouse\Product;
use core\models\warehouse\Usage;
use core\models\warehouse\UsageProduct;
use core\services\warehouse\dto\UsageDto;
use core\services\warehouse\dto\UsageProductDto;
use core\services\warehouse\UsageService;

class UsageServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var UsageService
     */
    private $service;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->service = \Yii::createObject(UsageService::class);
    }

    protected function _after()
    {
    }

    public function testCreate()
    {
        $division = $this->tester->getFactory()->create(Division::class);
        $product = $this->tester->getFactory()->create(Product::class, ['quantity' => 100]);

        $quantity = 2;
        $comments = $this->tester->getFaker()->text(20);
        $usageData = new UsageDto(
            $division->company_id,
            $division->id,
            null,
            null,
            0,
            $comments
        );
        $productsData = [
            new UsageProductDto(
                $product->id,
                $quantity
            )
        ];

        $usage = $this->service->create($usageData, $productsData);

        verify($usage->id)->notNull();
        verify($usage)->isInstanceOf(Usage::class);
        $this->tester->seeRecord(Usage::class, [
            'id'          => $usage->id,
            'company_id'  => $division->company_id,
            'division_id' => $division->id,
            'comments'    => $comments
        ]);
        $this->tester->seeRecord(UsageProduct::class, [
            'product_id'     => $product->id,
            'usage_id'       => $usage->id,
            'purchase_price' => $product->purchase_price,
            'selling_price'  => $product->price,
            'quantity'       => $quantity
        ]);
    }

    /**
     * @dataProvider cancelProvider
     * @param int $status
     * @param string $exception
     */
    public function testCancel(int $status, string $exception = null)
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $productCount = 100;
        $usageProductQty = 5;

        $division = $this->tester->getFactory()->create(Division::class);
        $product = $this->tester->getFactory()->create(Product::class, ['quantity' => $productCount]);

        $usage = $this->tester->getFactory()->create(Usage::class, [
            'division_id' => $division->id,
            'company_id'  => $division->company_id,
            'status'      => $status
        ]);
        $usageProduct = $this->tester->getFactory()->create(UsageProduct::class, [
            'usage_id'       => $usage->id,
            'product_id'     => $product->id,
            'quantity'       => $usageProductQty,
            'purchase_price' => $product->purchase_price,
            'selling_price'  => $product->price
        ]);

        $usage = $this->service->cancel($usage->id);

        verify($usage)->isInstanceOf(Usage::class);
        $this->tester->seeRecord(Usage::class, [
            'id'          => $usage->id,
            'company_id'  => $division->company_id,
            'division_id' => $division->id,
            'status'      => Usage::STATUS_CANCELED
        ]);

        $this->tester->seeRecord(Product::class, [
            'id'       => $product->id,
            'quantity' => $productCount + $usageProductQty
        ]);

    }

    /**
     * @return array
     */
    public function cancelProvider()
    {
        return [
            [Usage::STATUS_CANCELED, \DomainException::class],
            [Usage::STATUS_ACTIVE, null],
        ];
    }

    /**
     * @return array
     */
    public function updateProvider()
    {
        return [
            [Usage::STATUS_CANCELED, null],
            [Usage::STATUS_ACTIVE, \DomainException::class],
        ];
    }
}