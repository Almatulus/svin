<?php

namespace app\tests\unit\modules\finance\services;

use Codeception\Specify;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\user\User;
use core\repositories\CompanyCostItemRepository;
use core\services\CompanyCashService;

class CompanyCashServiceTest extends \Codeception\Test\Unit
{
    use Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var CompanyCashService
     */
    private $cashService;

    /**
     * @var Division
     */
    private $division;

    protected function _before()
    {
        $this->cashService = \Yii::createObject(CompanyCashService::class);

        if (!$this->division) {
            $this->division = $this->tester->getFactory()->create(Division::class);
        }
    }


    /**
     * @param bool $isNull
     * @param string $exception
     * @dataProvider addProvider
     */
    public function testAdd(bool $isNull, string $exception = null)
    {
        $division_id = 0;
        if (!$isNull) {
            $division_id = $this->division->id;
        }

        if ($exception) {
            $this->expectException($exception);
        }

        $cash = $this->cashService->add(
            $this->tester->getFaker()->text(30),
            $this->tester->getFaker()->randomNumber(3),
            true,
            $this->tester->getFaker()->text(10),
            CompanyCash::TYPE_CASH_BOX,
            $division_id
        );

        expect("Cash Model", $cash)->isInstanceOf(CompanyCash::class);
        expect("Cash Model id is not empty", $cash->id)->notNull();
    }

    public function testEdit()
    {
        $cash = $this->tester->getFactory()->create(CompanyCash::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id
        ]);

        $comments = $this->tester->getFaker()->text(14);
        $name = $this->tester->getFaker()->name;
        $init_money = $this->tester->getFaker()->randomNumber(4);

        $this->cashService->edit($cash->id, $comments, $name, $init_money);

        $this->tester->canSeeRecord(CompanyCash::class, [
            'id'         => $cash->id,
            'comments'   => $comments,
            'name'       => $name,
            'init_money' => $init_money
        ]);
    }

    public function testDelete()
    {
        $cash = $this->tester->getFactory()->create(CompanyCash::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id
        ]);

        $this->cashService->delete($cash->id);

        $this->tester->canSeeRecord(CompanyCash::class, [
            'id'     => $cash->id,
            'status' => CompanyCash::STATUS_DISABLED
        ]);
    }

    public function testTransfer()
    {
        $initialCashBalance = 5000;
        $cash = $this->tester->getFactory()->create(CompanyCash::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id,
            'init_money'  => $initialCashBalance
        ]);

        $initialTargetCashBalance = 0;
        $targetCash = $this->tester->getFactory()->create(CompanyCash::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id,
            'init_money'  => $initialTargetCashBalance
        ]);

        $user = $this->tester->getFactory()->create(User::class, ['company_id' => $this->division->company_id]);
        \Yii::$app->user->setIdentity($user);

        $transferCash = 4000;
        $this->cashService->transfer($cash->id, $targetCash->id, $transferCash, $user->id);

        verify($cash->balance)->equals($initialCashBalance - $transferCash);
        verify($targetCash->balance)->equals($initialTargetCashBalance + $transferCash);

        $costItemRepository = new CompanyCostItemRepository();
        $incomeTransferCostItem = $costItemRepository->findCashTransferIncome($cash->division->company_id);
        $expenseTransferCostItem = $costItemRepository->findCashTransferExpense($cash->division->company_id);

        $this->tester->canSeeRecord(CompanyCashflow::class, [
            'cost_item_id' => $expenseTransferCostItem->id,
            'is_deleted'   => false,
            'cash_id'      => $cash->id,
            'value'        => $transferCash,
            'user_id'      => $user->id,
            'division_id'  => $this->division->id,
            'company_id'   => $this->division->company_id,
        ]);

        $this->tester->canSeeRecord(CompanyCashflow::class, [
            'cost_item_id' => $incomeTransferCostItem->id,
            'is_deleted'   => false,
            'cash_id'      => $targetCash->id,
            'value'        => $transferCash,
            'user_id'      => $user->id,
            'division_id'  => $this->division->id,
            'company_id'   => $this->division->company_id,
        ]);
    }

    public function testTransferToTheSameCash()
    {
        $this->expectException(\DomainException::class);

        $initialCashBalance = 5000;
        $cash = $this->tester->getFactory()->create(CompanyCash::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id,
            'init_money'  => $initialCashBalance
        ]);
        $user = $this->tester->getFactory()->create(User::class, ['company_id' => $this->division->company_id]);
        \Yii::$app->user->setIdentity($user);

        $transferCash = 4000;
        $this->cashService->transfer($cash->id, $cash->id, $transferCash, $user->id);
    }

    public function testTransferWithInsufficientBalance()
    {
        $this->expectException(\DomainException::class);

        $initialCashBalance = 0;
        $cash = $this->tester->getFactory()->create(CompanyCash::class, [
            'division_id' => $this->division->id,
            'company_id'  => $this->division->company_id,
            'init_money'  => $initialCashBalance
        ]);
        $user = $this->tester->getFactory()->create(User::class, ['company_id' => $this->division->company_id]);
        \Yii::$app->user->setIdentity($user);

        $transferCash = 4000;
        $this->cashService->transfer($cash->id, $cash->id, $transferCash, $user->id);
    }

    public function addProvider()
    {
        return [
            [true, \core\repositories\exceptions\NotFoundException::class],
            [false, null]
        ];
    }

}