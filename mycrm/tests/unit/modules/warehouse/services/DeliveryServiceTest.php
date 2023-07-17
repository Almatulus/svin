<?php

namespace modules\warehouse\services;

use core\forms\warehouse\delivery\DeliveryCreateForm;
use core\forms\warehouse\delivery\DeliveryUpdateForm;
use core\models\company\Company;
use core\models\division\Division;
use core\models\finance\CompanyContractor;
use core\models\user\User;
use core\models\warehouse\Category;
use core\models\warehouse\Delivery;
use core\models\warehouse\DeliveryProduct;
use core\models\warehouse\Product;
use core\services\warehouse\DeliveryService;

class DeliveryServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var DeliveryService
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
     * @var Product
     */
    private $product;

    /**
     * @var DeliveryProduct []
     */
    private $deliveryProducts;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Company
     */
    private $company;

    /**
     * @var CompanyContractor
     */
    private $contractor;

    /**
     * @throws \Exception
     */
    public function testCreate()
    {
        $form = new DeliveryCreateForm([
            'division_id' => $this->division->id,
            'contractor_id' => $this->contractor->id,
            'invoice_number' => $this->tester->getFaker()->randomNumber(),
            'notes' => $this->tester->getFaker()->text
        ]);

        $model = $this->service->create(
            $this->company->id,
            $this->user->id,
            $form->contractor_id,
            $form->division_id,
            $form->invoice_number,
            $form->delivery_date,
            $form->notes,
            $this->deliveryProducts
        );

        $attribute_keys = array_keys($form->getAttributes());

        verify($model)->isInstanceOf(Delivery::class);
        verify($model->id)->notNull();
        verify($model->getAttributes($attribute_keys))->equals($form->getAttributes($attribute_keys));

        $this->tester->canSeeRecord(DeliveryProduct::className(), [
            'delivery_id' => $model->id
        ]);
    }

    /**
     * @throws \Exception
     */
    public function testEdit()
    {
        $delivery = $this->service->create(
            $this->company->id,
            $this->user->id,
            $this->contractor->id,
            $this->division->id,
            $this->tester->getFaker()->randomNumber(),
            date('Y-m-d'),
            $this->tester->getFaker()->text(),
            $this->deliveryProducts);

        $newDivision = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->company->id
        ]);
        $newContractor = $this->contractor = $this->tester->getFactory()->create(CompanyContractor::class, [
            'division_id' => $newDivision->id
        ]);

        $form = new DeliveryUpdateForm($delivery);
        $form->setAttributes([
            'invoice_number' => $this->tester->getFaker()->randomNumber(),
            'notes' => $this->tester->getFaker()->text(),
            'division_id' => $newDivision->id,
            'contractor_id' => $newContractor->id,
            'delivery_date' => date('Y-m-d', strtotime('+1 week'))
        ]);

        $model = $this->service->edit(
            $delivery->id,
            $form->contractor_id,
            $form->division_id,
            $form->invoice_number,
            $form->delivery_date,
            $form->notes,
            $this->deliveryProducts
        );

        $attribute_keys = array_diff(array_keys($form->getAttributes()), ["delivery"]);

        verify($model)->isInstanceOf(Delivery::class);
        verify($model->id)->notNull();
        verify($model->getAttributes($attribute_keys))->equals($form->getAttributes($attribute_keys));

        $this->tester->canSeeRecord(DeliveryProduct::className(), [
            'delivery_id' => $model->id
        ]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function _before()
    {
        $this->service = \Yii::createObject(DeliveryService::class);

        $this->company = $this->tester->getFactory()->create(Company::class);

        $this->category = $this->tester->getFactory()->create(Category::class,[
            'company_id' => $this->company->id
        ]);

        $this->division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $this->company->id
        ]);

        $this->contractor = $this->tester->getFactory()->create(CompanyContractor::class, [
            'division_id' => $this->division->id
        ]);

        $this->product = $this->tester->getFactory()->create(Product::class, [
            'company_id' => $this->category->company_id,
            'category_id' => $this->category->id
        ]);

        $deliveryProduct = new DeliveryProduct();
        $deliveryProduct->product_id = $this->product->id;
        $deliveryProduct->quantity = 1;
        $deliveryProduct->price = $this->product->price;

        $this->deliveryProducts = [$deliveryProduct];

        $this->user = $this->tester->getFactory()->create(User::class);
    }

    protected function _after()
    {

    }

}