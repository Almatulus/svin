<?php

namespace modules\warehouse\services;

use core\models\warehouse\Category;
use core\services\warehouse\CategoryService;
use core\models\company\Company;

class CategoryServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var CategoryService
     */
    private $service;

    /**
     * @var Company
     */
    private $company;


    public function testCreate()
    {
        $model = $this->service->create($this->tester->getFaker()->name, $this->company->id);

        verify($model)->isInstanceOf(Category::class);
        verify($model->id)->notNull();
    }

    public function testEdit()
    {
        $category = $this->tester->getFactory()->create(Category::class,[
            'company_id' => $this->company->id,
        ]);

        $name = $this->tester->getFaker()->name;
        $model = $this->service->edit($category->id, $name);

        verify($model->name)->equals($name);
    }


    protected function _before()
    {
        $this->service = \Yii::createObject(CategoryService::class);

        $this->company = $this->tester->getFactory()->create(Company::class);
    }

    protected function _after()
    {

    }

}