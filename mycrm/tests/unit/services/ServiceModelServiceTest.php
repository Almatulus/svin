<?php

namespace services;


use core\forms\division\ServiceCreateForm;
use core\forms\division\ServiceUpdateForm;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\division\DivisionServiceInsuranceCompany;
use core\models\division\DivisionServiceProduct;
use core\models\InsuranceCompany;
use core\models\ServiceCategory;
use core\models\Staff;
use core\models\warehouse\Product;
use core\services\division\ServiceModelService;

class ServiceModelServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ServiceModelService
     */
    private $service;
    /**
     * @var Division
     */
    private $division;

    /**
     * @var InsuranceCompany[]
     */
    private $insuranceCompanies;

    /**
     * @var ServiceCategory[]
     */
    private $categories;

    /**
     * @var Staff[]
     */
    private $staffs;

    /**
     * @var Product[]
     */
    private $products;

    /**
     * @var DivisionServiceInsuranceCompany[]
     */
    private $divisionServiceInsuranceCompanies = [];

    /**
     * @var DivisionServiceProduct[]
     */
    private $divisionServiceProducts = [];

    public function testCreate()
    {
        $attributes = [
            'average_time'         => $this->tester->getFaker()->randomNumber(2),
            'category_ids'         => [$this->categories[0]->id, $this->categories[1]->id],
            'description'          => $this->tester->getFaker()->text(20),
            'division_ids'         => [$this->division->id],
            'insurance_company_id' => $this->insuranceCompanies[0]->id,
            'is_trial'             => true,
            'price'                => $this->tester->getFaker()->randomNumber(3),
            'price_max'            => $this->tester->getFaker()->randomNumber(3),
            'publish'              => true,
            'service_name'         => $this->tester->getFaker()->name,
            'staff'                => [$this->staffs[0]->id, $this->staffs[1]->id],
        ];

        $form = new ServiceCreateForm();
        $form->setAttributes($attributes);

        $service = $this->service->create($form, $this->divisionServiceProducts, $this->divisionServiceInsuranceCompanies);

        verify($service)->isInstanceOf(DivisionService::class);
        verify($service->id)->notNull();

        $this->tester->canSeeRecord(DivisionService::class, array_diff_key($attributes, [
            'category_ids' => 'category_ids',
            'division_ids' => 'division_ids',
            'staff'        => 'staff'
        ]));

        foreach ($this->divisionServiceProducts as $product) {
            $this->tester->canSeeRecord(DivisionServiceProduct::class, [
                'division_service_id' => $service->id,
                'product_id'          => $product->product_id,
                'quantity'            => $product->quantity
            ]);
        }

        foreach ($this->divisionServiceInsuranceCompanies as $divisionServiceInsuranceCompany){
            $this->tester->canSeeRecord(DivisionServiceInsuranceCompany::class, [
                'division_service_id'   => $service->id,
                'insurance_company_id'  => $divisionServiceInsuranceCompany->insurance_company_id,
                'price'                 => $divisionServiceInsuranceCompany->price,
                'price_max'             => $divisionServiceInsuranceCompany->price_max
            ]);
        }

        verify($service->getCategories()->count())->equals(sizeof($attributes['category_ids']));
        verify($service->getDivisions()->count())->equals(sizeof($attributes['division_ids']));
        verify($service->getStaffs()->count())->equals(sizeof($attributes['staff']));
    }

    public function testUpdate()
    {
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $divisionService->link('divisions', $this->division);

        $anotherInsuranceCompany = $this->tester->getFactory()->create(InsuranceCompany::class);
        $anotherProduct = $this->tester->getFactory()->create(Product::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id
        ]);

        $existingDivisionServiceInsuranceCompany =
            $this->tester->getFactory()->create(DivisionServiceInsuranceCompany::class, [
                'division_service_id' => $divisionService->id,
                'insurance_company_id' => $anotherInsuranceCompany->id,
            ]);

        $existingDivisionProduct = $this->tester->getFactory()->create(DivisionServiceProduct::class, [
            'product_id' => $anotherProduct->id,
            'quantity' => rand(1, 10),
            'division_service_id' => $divisionService->id
        ]);

        $attributes = [
            'average_time'         => $this->tester->getFaker()->randomNumber(2),
            'category_ids'         => [$this->categories[0]->id, $this->categories[1]->id],
            'description'          => $this->tester->getFaker()->text(20),
            'division_ids'         => [$this->division->id],
            'insurance_company_id' => $this->insuranceCompanies[0]->id,
            'is_trial'             => true,
            'price'                => $this->tester->getFaker()->randomNumber(3),
            'price_max'            => $this->tester->getFaker()->randomNumber(3),
            'publish'              => true,
            'service_name'         => $this->tester->getFaker()->name,
            'staff'                => [$this->staffs[0]->id, $this->staffs[1]->id],
        ];

        $form = new ServiceUpdateForm($divisionService->id);
        $form->setAttributes($attributes);

        //  check if old one exists
        $this->tester->canSeeRecord(DivisionServiceInsuranceCompany::class, [
            'division_service_id'   => $existingDivisionServiceInsuranceCompany->division_service_id,
            'insurance_company_id'  => $existingDivisionServiceInsuranceCompany->insurance_company_id
        ]);
        $this->tester->canSeeRecord(DivisionServiceProduct::class, [
            'division_service_id' => $existingDivisionProduct->division_service_id,
            'product_id'            => $existingDivisionProduct->product_id
        ]);

        $service = $this->service->update(
            $divisionService->id,
            $form,
            $this->divisionServiceProducts,
            $this->divisionServiceInsuranceCompanies
        );

        verify($service)->isInstanceOf(DivisionService::class);
        verify($service->id)->notNull();

        $this->tester->canSeeRecord(DivisionService::class, array_merge(['id' => $divisionService->id],
                array_diff_key($attributes, [
                    'category_ids' => 'category_ids',
                    'division_ids' => 'division_ids',
                    'staff'        => 'staff'
                ])
            )
        );

        //  check if new ones were added
        foreach ($this->divisionServiceProducts as $product) {
            $this->tester->canSeeRecord(DivisionServiceProduct::class, [
                'division_service_id' => $service->id,
                'product_id'          => $product->product_id,
                'quantity'            => $product->quantity
            ]);
        }
        foreach ($this->divisionServiceInsuranceCompanies as $divisionServiceInsuranceCompany){
            $this->tester->canSeeRecord(DivisionServiceInsuranceCompany::class, [
                'division_service_id'   => $service->id,
                'insurance_company_id'  => $divisionServiceInsuranceCompany->insurance_company_id,
                'price'                 => $divisionServiceInsuranceCompany->price,
                'price_max'             => $divisionServiceInsuranceCompany->price_max
            ]);
        }

        //  check if not selected are deleted
        $this->tester->cantSeeRecord(DivisionServiceInsuranceCompany::class, [
            'division_service_id'   => $existingDivisionServiceInsuranceCompany->division_service_id,
            'insurance_company_id'  => $existingDivisionServiceInsuranceCompany->insurance_company_id
        ]);
        $this->tester->cantSeeRecord(DivisionServiceProduct::class, [
            'division_service_id'   => $existingDivisionProduct->division_service_id,
            'product_id'            => $existingDivisionProduct->product_id
        ]);

        verify($service->getCategories()->count())->equals(sizeof($attributes['category_ids']));
        verify($service->getDivisions()->count())->equals(sizeof($attributes['division_ids']));
        verify($service->getStaffs()->count())->equals(sizeof($attributes['staff']));
    }

    // tests

    public function testDelete()
    {
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);

        $this->service->delete($divisionService->id);

        $this->tester->canSeeRecord(DivisionService::class, [
            'id'     => $divisionService->id,
            'status' => DivisionService::STATUS_DELETED
        ]);
    }

    protected function _before()
    {
        $this->service = \Yii::createObject(ServiceModelService::class);

        if (!$this->division) {
            $this->division = $this->tester->getFactory()->create(Division::class);
        }

        $this->insuranceCompanies = $this->tester->getFactory()->seed(5,InsuranceCompany::class);
        $this->categories = $this->tester->getFactory()->seed(2, ServiceCategory::class);
        $this->staffs = $this->tester->getFactory()->seed(2, Staff::class);
        $this->products = $this->tester->getFactory()->seed(2, Product::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id
        ]);

        /**
         * @var DivisionServiceInsuranceCompany[] $divisionServiceInsuranceCompanies
         */
        foreach ($this->insuranceCompanies as $insuranceCompany){
            $this->divisionServiceInsuranceCompanies[] = new DivisionServiceInsuranceCompany([
                'insurance_company_id' => $insuranceCompany->id,
                'price' => rand(100, 1000),
                'price_max' => rand(1000, 10000)
            ]);
        }

        $this->divisionServiceProducts = [
            new DivisionServiceProduct(['product_id' => $this->products[0]->id, 'quantity' => rand(1, 10)]),
            new DivisionServiceProduct(['product_id' => $this->products[1]->id, 'quantity' => rand(1, 10)])
        ];
    }

    protected function _after()
    {
    }
}