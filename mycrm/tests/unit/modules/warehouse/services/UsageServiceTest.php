<?php

namespace modules\warehouse\services;

use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\Staff;
use core\models\warehouse\Product;
use core\models\warehouse\Usage;
use core\models\warehouse\UsageProduct;
use core\services\warehouse\dto\UsageDto;
use core\services\warehouse\dto\UsageProductDto;
use core\services\warehouse\UsageService;
use core\models\company\Company;

class UsageServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var UsageService
     */
    private $service;

    /**
     * @var Division
     */
    private $division;

    /**
     * @var CompanyCustomer
     */
    private $companyCustomer;

    /**
     * @var Staff
     */
    private $staff;

    /**
     * @var UsageProduct
     */
    private $usageProduct;

    /**
     * @var Company
     */
    private $company;

    public function testCreate()
    {
        $usageDto = new UsageDto($this->company->id, $this->division->id, $this->companyCustomer->id,
            $this->staff->id, 0, "");

        $usageProductDto = new UsageProductDto($this->usageProduct->product_id,
            $this->usageProduct->quantity, $this->usageProduct->id);

        $model = $this->service->create($usageDto, [$usageProductDto]);

        verify($model)->isInstanceOf(Usage::class);
        verify($model->id)->notNull();
    }

    public function testUpdate()
    {
        $usage = $this->tester->getFactory()->create(Usage::class,[
            'company_id' => $this->company->id,
            'company_customer_id' => $this->companyCustomer->id,
            'division_id' => $this->division->id,
            'staff_id' => $this->staff->id,
            'status' => Usage::STATUS_CANCELED
        ]);

        $newStaff = $this->tester->getFactory()->create(Staff::class, []);
        $newCompanyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class);

        $usageDto = new UsageDto($this->company->id, $this->division->id, $newCompanyCustomer->id,
            $newStaff->id, 42, "new comments");

        $usageProductDto = new UsageProductDto($this->usageProduct->product_id,
            $this->usageProduct->quantity, $this->usageProduct->id);

        $model = $this->service->update($usage->id, $usageDto, [$usageProductDto]);

        verify($model->discount)->equals($usageDto->getDiscount());
        verify($model->comments)->equals($usageDto->getComments());
        verify($model->staff_id)->equals($usageDto->getStaffId());
        verify($model->company_customer_id)->equals($usageDto->getCompanyCustomerId());
    }



    protected function _before()
    {
        $this->service = \Yii::createObject(UsageService::class);

        $this->company = $this->tester->getFactory()->create(Company::class);

        $this->division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->company->id
        ]);

        $this->companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class);

        $this->staff = $this->tester->getFactory()->create(Staff::class, []);

        $product = $this->tester->getFactory()->create(Product::class, [
            'company_id' => $this->company->id
        ]);

        $this->usageProduct = new UsageProduct();
        $this->usageProduct->product_id = $product->id;
        $this->usageProduct->quantity = 1;
    }

    protected function _after()
    {

    }

}