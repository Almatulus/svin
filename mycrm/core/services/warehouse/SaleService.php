<?php

namespace core\services\warehouse;

use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCashflowPayment;
use core\models\finance\CompanyCashflowProduct;
use core\models\Payment;
use core\models\warehouse\Sale;
use core\models\warehouse\SaleProduct;
use core\repositories\CompanyCostItemRepository;
use core\repositories\division\DivisionRepository;
use core\repositories\user\UserRepository;
use core\repositories\warehouse\SaleRepository;
use core\services\TransactionManager;
use Yii;

class SaleService
{
    private $divisionRepository;
    private $costItemRepository;
    private $saleRepository;
    private $transactionManager;
    private $userRepository;

    /**
     * UsageService constructor.
     * @param DivisionRepository $divisionRepository
     * @param SaleRepository $saleRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        CompanyCostItemRepository $costItemRepository,
        DivisionRepository $divisionRepository,
        SaleRepository $saleRepository,
        TransactionManager $transactionManager,
        UserRepository $userRepository
    ) {
        $this->costItemRepository = $costItemRepository;
        $this->divisionRepository = $divisionRepository;
        $this->saleRepository = $saleRepository;
        $this->transactionManager = $transactionManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @param $cash_id
     * @param $company_customer_id
     * @param $discount
     * @param $division_id
     * @param $paid
     * @param $payment_id
     * @param $sale_date
     * @param $staff_id
     * @param \core\models\warehouse\SaleProduct[] $saleProducts
     * @return Sale
     * @throws \Exception
     */
    public function create($cash_id, $company_customer_id, $discount, $division_id,
        $paid, $payment_id, $sale_date, $staff_id, $saleProducts)
    {
        $division = $this->divisionRepository->find($division_id);

        $sale = Sale::create(
            $division,
            $cash_id,
            $company_customer_id,
            $discount,
            $paid,
            $payment_id,
            $sale_date,
            $staff_id
        );

        $this->transactionManager->execute(function () use ($sale, $saleProducts) {
            $this->saleRepository->add($sale);
            $this->insertProducts($sale, $saleProducts);
            $this->writeOff($saleProducts);
            $this->checkout($sale, $saleProducts);
        });

        return $sale;
    }

    /**
     * @param $id
     * @param $cash_id
     * @param $company_customer_id
     * @param $discount
     * @param $division_id
     * @param $paid
     * @param $payment_id
     * @param $sale_date
     * @param $staff_id
     * @param $productsToDelete
     * @param \core\models\warehouse\SaleProduct[] $saleProducts
     * @return Sale
     * @throws \Exception
     */
    public function edit($id, $cash_id, $company_customer_id, $discount, $division_id,
        $paid, $payment_id, $sale_date, $staff_id, $productsToDelete, $saleProducts)
    {
        $division = $this->divisionRepository->find($division_id);
        $sale = $this->saleRepository->find($id);

        $sale->edit(
            $division,
            $cash_id,
            $company_customer_id,
            $discount,
            $paid,
            $payment_id,
            $sale_date,
            $staff_id
        );

        $this->transactionManager->execute(function () use ($sale, $productsToDelete, $saleProducts) {
            $this->saleRepository->edit($sale);
            $this->clearProducts($productsToDelete);
            $this->insertProducts($sale, $saleProducts);
            $this->writeOff($saleProducts);
            $this->checkout($sale, $saleProducts);
        });

        return $sale;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function delete($id)
    {
        $sale = $this->saleRepository->find($id);
        $productsToDelete = $sale->getSaleProducts()->select('id')->column();

        $this->transactionManager->execute(function () use ($sale, $productsToDelete) {
            $this->clearProducts($productsToDelete);

            // delete cashflow
            $cashflow = $sale->cashflow;
            if ($cashflow) {
                $this->saleRepository->unlinkSaleWithCashflow($sale->id);
                $this->saleRepository->delete($cashflow);
            }

            $this->saleRepository->delete($sale);
        });
    }

    /**
     * @param Sale $sale
     * @param SaleProduct[] $saleProducts
     */
    private function insertProducts(Sale $sale, $saleProducts)
    {
        foreach ($saleProducts as $saleProduct) {
            $saleProduct->sale_id = $sale->id;
            if ($saleProduct->isNewRecord) {
                $this->saleRepository->add($saleProduct);
            } else {
                // revert quantity of product in stock after previous writeOff
                $saleProduct->product->revertWriteOff($saleProduct->oldAttributes['quantity']);
                $this->saleRepository->edit($saleProduct);
                $this->saleRepository->edit($saleProduct->product);
            }
        }
    }

    /**
     * Delete sale product and revert product quanity after write-off
     * @param int[] $productsToDelete
     */
    private function clearProducts($productsToDelete)
    {
        foreach ($productsToDelete as $key => $product_id) {
            $saleProduct = $this->saleRepository->findProduct($product_id);

            // revert quantity of product in stock after previous writeOff
            $saleProduct->product->revertWriteOff($saleProduct->quantity);
            $this->saleRepository->edit($saleProduct->product);

            // delete product
            $this->saleRepository->delete($saleProduct);
        }
    }

    /**
     * Write off, decrease quantity of products in stock
     */
    private function writeOff($saleProducts)
    {
        foreach ($saleProducts as $key => $saleProduct) {
            if ($saleProduct->product->quantity < $saleProduct->quantity) {
                throw new \DomainException(Yii::t('app', 'There are fewer items in stock than declared in the sale form'));
            }
            $saleProduct->product->writeOff($saleProduct->quantity);
            $this->saleRepository->edit($saleProduct->product);
        }
        return true;
    }

    /**
     * Creates cashflow for each product
     * @param Sale $sale
     * @param SaleProduct[] $saleProducts
     */
    private function checkout($sale, $saleProducts)
    {
        $costItem = $this->costItemRepository->findOrderProductCostItemByCompany($sale->division->company_id);
        $cashflow = $sale->cashflow;
        if (!$cashflow) {
            $cashflow = CompanyCashflow::add(
                $sale->sale_date . date(" H:i:s"),
                $sale->cash_id,
                "",
                $sale->division->company_id,
                null,
                $costItem->id,
                $sale->company_customer_id,
                $sale->division_id,
                CompanyCashflow::RECEIVER_STAFF,
                $sale->staff_id,
                $sale->getTotalCost(),
                Yii::$app->user->id
            );
            $this->saleRepository->add($cashflow);

            $this->saleRepository->linkSaleWithCashflow($cashflow->id, $sale->id);
        } else {
            $cashflow->edit(
                $sale->sale_date . date(" H:i:s"),
                $sale->cash_id,
                "",
                $sale->division->company_id,
                null,
                $costItem->id,
                $sale->company_customer_id,
                $sale->division_id,
                CompanyCashflow::RECEIVER_STAFF,
                $sale->staff_id,
                $sale->getTotalCost(),
                Yii::$app->user->id
            );
            $this->saleRepository->edit($cashflow);
            $cashflow->unlinkAll('products', true);
            $cashflow->unlinkAll('payments', true);
        }

        foreach ($saleProducts as $saleProduct) {
            $cashflowProduct = new CompanyCashflowProduct([
                'product_id'  => $saleProduct->product_id,
                'cashflow_id' => $cashflow->id,
                'discount'    => $saleProduct->discount,
                'quantity'    => $saleProduct->quantity,
                'price'       => $saleProduct->price
            ]);
            $this->saleRepository->add($cashflowProduct);
        }

        $payment_id = $sale->payment_id ?: Payment::CASH_ID;
        $cashflowPayment = CompanyCashflowPayment::add($cashflow, $payment_id, $sale->getTotalCost());
        $this->saleRepository->add($cashflowPayment);
    }

    /**
     * @param $models
     */
    public function export($models)
    {
        $totalIncome = $totalCost = 0;
        foreach ($models as $key => $product) {
            $totalIncome += $product->income;
            $totalCost += $product->totalCost;
        }

        $columns = [
            ['attribute' => 'product.sku'],
            ['attribute' => 'product.name'],
            ['attribute' => 'quantity', 'format' => 'number'],
            ['attribute' => 'product.unit.name', 'label' => Yii::t('app', 'Unit')],
            ['attribute' => 'purchase_price', 'format' => 'number'],
            ['attribute' => 'extraCharge', 'format' => 'number'],
            [
                'attribute' => 'extraChargeRate',
                'format'    => 'number',
                'value'     => function (SaleProduct $model) {
                    return $model->getExtraChargeRate() * 100;
                }
            ],
            ['attribute' => 'price', 'format' => 'number'],
            ['attribute' => 'totalCost', 'format' => 'number', 'footer' => $totalCost],
            ['attribute' => 'income', 'format' => 'number', 'footer' => $totalIncome],
            ['attribute' => 'sale.payment.label', 'label' => Yii::t('app', 'Payment')],
            ['attribute' => 'sale.staff.name', 'label' => Yii::t('app', 'Staff ID')],
            [
                'attribute' => 'sale.companyCustomer.customer.fullName',
                'label'     => Yii::t('app', 'Customer')
            ]
        ];

        $excelService = new \common\components\excel\Excel([
            'showFooter' => true,
            'models'     => $models,
            'columns'    => $columns,
            'creator'    => \Yii::$app->name,
            'title'      => \Yii::t('app', "Sales analysis"),
            'filename'   => \Yii::t('app', "Sales analysis") . "_" . date("d-m-Y-His"),
        ]);
        $excelService->export();
    }
}
