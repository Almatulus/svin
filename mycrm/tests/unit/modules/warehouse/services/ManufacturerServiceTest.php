<?php

namespace modules\warehouse\services;

use core\models\warehouse\Manufacturer;
use core\services\warehouse\ManufacturerService;
use core\models\company\Company;

class ManufacturerServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ManufacturerService
     */
    private $service;

    /**
     * @var Company
     */
    private $company;


    public function testCreate()
    {
        $model = $this->service->create($this->tester->getFaker()->name, $this->company->id);

        verify($model)->isInstanceOf(Manufacturer::class);
        verify($model->id)->notNull();
    }

    public function testEdit()
    {
        $manufacturer = $this->tester->getFactory()->create(Manufacturer::class,[
            'company_id' => $this->company->id,
        ]);

        $name = $this->tester->getFaker()->name;
        $model = $this->service->edit($manufacturer->id, $name);

        verify($model->name)->equals($name);
    }


    protected function _before()
    {
        $this->service = \Yii::createObject(ManufacturerService::class);

        $this->company = $this->tester->getFactory()->create(Company::class);
    }

    protected function _after()
    {

    }

}