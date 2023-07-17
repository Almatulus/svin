<?php

namespace core\services;

use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\repositories\CompanyCashflowRepository;
use core\repositories\CompanyCashRepository;
use core\repositories\CompanyCostItemRepository;
use core\repositories\division\DivisionRepository;

class CompanyCashService
{
    private $divisionRepository;
    private $cashflowRepository;
    private $companyCashRepository;
    private $costItemRepository;
    private $transactionManager;

    /**
     * CompanyCashService constructor.
     * @param DivisionRepository $divisionRepository
     * @param CompanyCashRepository $companyCashRepository
     * @param CompanyCashflowRepository $cashflowRepository
     * @param CompanyCostItemRepository $costItemRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        DivisionRepository $divisionRepository,
        CompanyCashRepository $companyCashRepository,
        CompanyCashflowRepository $cashflowRepository,
        CompanyCostItemRepository $costItemRepository,
        TransactionManager $transactionManager
    )
    {
        $this->divisionRepository = $divisionRepository;
        $this->companyCashRepository = $companyCashRepository;
        $this->costItemRepository = $costItemRepository;
        $this->cashflowRepository = $cashflowRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param $comments
     * @param $company_id
     * @param $init_money
     * @param $is_deletable
     * @param $name
     * @param $type
     * @return CompanyCash
     * @throws \Exception
     */
    public function add($comments, $init_money, $is_deletable, $name, $type, $division_id)
    {
        $division = $this->divisionRepository->find($division_id);

        $companyCash = CompanyCash::add(
            $division,
            $name,
            $type,
            $init_money,
            $comments,
            $is_deletable
        );

        $this->transactionManager->execute(function () use ($companyCash) {
            $this->companyCashRepository->add($companyCash);
        });

        return $companyCash;
    }

    /**
     * @param $id
     * @param $comments
     * @param $name
     * @param int $init_money
     * @return CompanyCash
     */
    public function edit($id, $comments, $name, int $init_money)
    {
        $companyCash = $this->companyCashRepository->find($id);

        $companyCash->edit(
            $comments,
            $name,
            $init_money
        );

        $this->companyCashRepository->edit($companyCash);

        return $companyCash;
    }

    /**
     * @param $id=
     * @return CompanyCash
     * @throws \Exception
     */
    public function delete($id)
    {
        $companyCash = $this->companyCashRepository->find($id);
        $companyCash->disable();

        $this->transactionManager->execute(function () use ($companyCash) {
            $this->companyCashRepository->edit($companyCash);
        });

        return $companyCash;
    }

    /**
     * @param int $id
     * @param int $target_id
     * @param int $amount
     * @param int $user_id
     * @return CompanyCash
     */
    public function transfer(int $id, int $target_id, int $amount, int $user_id)
    {
        if ($id == $target_id) {
            throw new \DomainException("Перевод в ту же кассу невозможен.");
        }

        $cash = $this->companyCashRepository->find($id);
        $targetCash = $this->companyCashRepository->find($target_id);

        if ($cash->balance < $amount) {
            throw new \DomainException("В кассе недостаточно денег для перевода данной суммы.");
        }

        $incomeCashTransfer = $this->costItemRepository->findCashTransferIncome($cash->division->company_id);
        $expenseCashTransfer = $this->costItemRepository->findCashTransferExpense($cash->division->company_id);

        $comment = "Перевод в кассу \"{$targetCash->name}\" на сумму " . \Yii::$app->formatter->asDecimal($amount);
        $cashflows[] = CompanyCashflow::add(date("Y-m-d H:i:s"), $cash->id, $comment, $cash->division->company_id,
            null, $expenseCashTransfer->id, null, $cash->division->id, CompanyCashflow::RECEIVER_CONTRACTOR,
            null, $amount, $user_id);

        $comment = "Перевод из кассы \"{$cash->name}\" на сумму " . \Yii::$app->formatter->asDecimal($amount);
        $cashflows[] = CompanyCashflow::add(date("Y-m-d H:i:s"), $targetCash->id, $comment,
            $targetCash->division->company_id,
            null, $incomeCashTransfer->id, null, $targetCash->division->id, CompanyCashflow::RECEIVER_CONTRACTOR,
            null, $amount, $user_id);

        $this->transactionManager->execute(function () use ($cashflows) {
            foreach ($cashflows as $cashflow) {
                $this->cashflowRepository->add($cashflow);
            }
        });

        return $cash;
    }
}
