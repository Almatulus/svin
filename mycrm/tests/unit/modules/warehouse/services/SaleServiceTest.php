<?php

namespace modules\warehouse\services;

use core\forms\warehouse\SaleForm;
use core\forms\warehouse\SaleUpdateForm;
use core\models\customer\CompanyCustomer;
use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\Payment;
use core\models\Staff;
use core\models\user\User;
use core\models\warehouse\Category;
use core\models\warehouse\Product;
use core\models\warehouse\Sale;
use core\models\warehouse\SaleProduct;
use core\services\warehouse\SaleService;

class SaleServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var SaleService
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
     * @var CompanyCash
     */
    private $cash;
    /**
     * @var CompanyCustomer
     */
    private $companyCustomer;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Staff
     */
    private $staff;

    /**
     * @var SaleProduct []
     */
    private $saleProducts;

    /**
     * @var Payment $payment
     */
    private $payment;

    public function testCreate()
    {
        $form = new SaleForm([
            'cash_id' => $this->cash->id,
            'company_customer_id' => $this->companyCustomer->id,
            'discount' => 10,
            'division_id' => $this->division->id,
            'paid' => 200,
            'payment_id' => $this->payment->id,
            'staff_id' => $this->staff->id,
            'sale_date' => date('Y-m-d')
        ]);

        $model = $this->service->create($form->cash_id, $form->company_customer_id, $form->discount, $form->division_id,
            $form->paid, $form->payment_id, $form->sale_date, $form->staff_id, $this->saleProducts);

        $attribute_keys = array_keys($form->getAttributes());

        verify($model)->isInstanceOf(Sale::class);
        verify($model->id)->notNull();
        verify($model->getAttributes($attribute_keys))->equals($form->getAttributes($attribute_keys));
    }

    public function testEdit()
    {
        $sale = $this->service->create($this->cash->id, $this->companyCustomer->id, 10, $this->division->id,
            200, $this->payment->id, date('Y-m-d'), $this->staff->id, $this->saleProducts);


        $newPayment = $this->tester->getFactory()->create(Payment::class);
        $newCash = $this->tester->getFactory()->create(CompanyCash::class, [
            'company_id' => $this->category->company_id
        ]);

        $form = new SaleUpdateForm($sale);
        $form->setAttributes([
            'discount' => 20,
            'payment_id' => $newPayment->id,
            'sale_date' => date('Y-m-d', strtotime("yesterday")),
            'cash_id' => $newCash->id
        ]);

        $model = $this->service->edit($sale->id, $form->cash_id, $form->company_customer_id, $form->discount, $form->division_id,
            $form->paid, $form->payment_id, $form->sale_date, $form->staff_id, [], $this->saleProducts);

        $attribute_keys = array_diff(array_keys($form->getAttributes()), ["sale"]);

        verify($model)->isInstanceOf(Sale::class);
        verify($model->id)->notNull();
        verify($model->getAttributes($attribute_keys))->equals($form->getAttributes($attribute_keys));
    }

    public function testDelete()
    {
        $sale = $this->service->create($this->cash->id, $this->companyCustomer->id, 10, $this->division->id,
            200, $this->payment->id, date('Y-m-d'), $this->staff->id, $this->saleProducts);

        $this->tester->canSeeRecord(Sale::class, [
            'id' => $sale->id
        ]);

        foreach($this->saleProducts as $saleProduct){
            $this->tester->canSeeRecord(SaleProduct::class, [
                'id' => $saleProduct->id
            ]);
        }

        $this->service->delete($sale->id);

        $this->tester->cantSeeRecord(Sale::class, [
            'id' => $sale->id
        ]);

        foreach($this->saleProducts as $saleProduct){
            $this->tester->cantSeeRecord(SaleProduct::class, [
                'id' => $saleProduct->id
            ]);
        }
    }

    protected function _before()
    {
        $this->service = \Yii::createObject(SaleService::class);

        $this->category = $this->tester->getFactory()->create(Category::class);
        $this->division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->category->company_id
        ]);

        $this->companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class);

        $this->cash = $this->tester->getFactory()->create(CompanyCash::class, [
            'company_id' => $this->category->company_id
        ]);

        $this->staff = $this->tester->getFactory()->create(Staff::class, []);

        $this->product = $this->tester->getFactory()->create(Product::class, [
            'company_id' => $this->category->company_id,
            'category_id' => $this->category->id
        ]);

        $saleProduct = new SaleProduct();
        $saleProduct->price = $this->product->price;
        $saleProduct->product_id = $this->product->id;
        $saleProduct->quantity = 1;

        $this->saleProducts = [$saleProduct];

        $this->payment = $this->tester->getFactory()->create(Payment::class);

        /**
         * @var User $user
         */
        $user = $this->tester->getFactory()->create(User::class);
        \Yii::$app->set('user', $user);
    }

    protected function _after()
    {

    }

}