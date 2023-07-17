<?php

namespace app\tests\unit\modules\finance\services;

use Codeception\Specify;
use core\models\company\Company;
use core\models\division\Division;
use core\models\finance\CompanyCostItem;
use core\models\finance\CompanyCostItemCategory;
use core\services\CompanyCostItemService;

class CompanyCostItemServiceTest extends \Codeception\Test\Unit
{
    use Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var CompanyCostItemService
     */
    private $service;

    /**
     * @var Company
     */
    private $company;
    /**
     * @var Division[]
     */
    private $divisions;

    protected function _before()
    {
        $this->service = \Yii::createObject(CompanyCostItemService::class);

        if (!$this->company) {
            $this->company = $this->tester->getFactory()->create(Company::class);
            $this->divisions = [
                $this->tester->getFactory()->create(Division::class, ['company_id' => $this->company->id]),
                $this->tester->getFactory()->create(Division::class, ['company_id' => $this->company->id])
            ];
        }
    }

    /**
     * @dataProvider addProvider
     * @param bool $isNull
     * @param string|null $exception
     */
    public function testAdd(bool $isNull, string $exception = null)
    {
        $company_id = 0;
        if (!$isNull) {
            $company_id = $this->company->id;
        }

        if ($exception) {
            $this->expectException($exception);
        }

        $costItem = $this->service->add(
            $this->tester->getFaker()->text(10),
            $company_id,
            $this->tester->getFaker()->name,
            CompanyCostItem::TYPE_INCOME,
            [
                $this->divisions[0]->id,
                $this->divisions[1]->id,
            ],
            null
        );
        expect("CostItem Model", $costItem)->isInstanceOf(CompanyCostItem::class);
        expect("CostItem Model id is not empty", $costItem->id)->notNull();
        expect("Validate costItem's divisions", $costItem->getDivisions()->select('id')->column())->equals([
            $this->divisions[0]->id,
            $this->divisions[1]->id
        ]);
    }

    public function testEdit()
    {
        $costItem = $this->tester->getFactory()->create(CompanyCostItem::class, [
            'company_id' => $this->company->id
        ]);

        $comments = $this->tester->getFaker()->text(20);
        $name = $this->tester->getFaker()->name;

        $costItem = $this->service->edit(
            $costItem->id,
            $comments,
            $this->company->id,
            $name,
            CompanyCostItem::TYPE_INCOME, [
                $this->divisions[0]->id,
                $this->divisions[1]->id
            ],
            null
        );

        expect("CostItem Model", $costItem)->isInstanceOf(CompanyCostItem::class);
        expect("CostItem Model id is not empty", $costItem->id)->notNull();
        expect("Validate costItem's divisions", $costItem->getDivisions()->select('id')->column())->equals([
            $this->divisions[0]->id,
            $this->divisions[1]->id
        ]);

        $this->tester->canSeeRecord(CompanyCostItem::class, [
            'id'         => $costItem->id,
            'comments'   => $comments,
            'name'       => $name,
            'type'       => CompanyCostItem::TYPE_INCOME,
            'company_id' => $this->company->id
        ]);
    }

    public function addProvider()
    {
        return [
            [true, \core\repositories\exceptions\NotFoundException::class],
            [false, null]
        ];
    }
}