<?php

namespace services\customer;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerLoyalty;
use core\models\order\Order;
use core\services\customer\LoyaltyManager;

class LoyaltyManagerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var LoyaltyManager
     */
    protected $loyaltyManager;

    public function testRewardWithDiscount()
    {
        /** @var CompanyCustomer $companyCustomer */
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, ['discount' => 0]);

        $discount = 10;

        // create loyalty program to grant 10% discount if customer revenue is greater than or equals 5000
        $this->tester->getFactory()->create(CustomerLoyalty::class, [
            'company_id' => $companyCustomer->company_id,
            'event'      => CustomerLoyalty::EVENT_MONEY,
            'mode'       => CustomerLoyalty::MODE_ADD_DISCOUNT,
            'amount'     => 5000,
            'discount'   => $discount
        ]);

        $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'status'              => OrderConstants::STATUS_FINISHED,
            'price'               => 5000
        ]);

        $this->loyaltyManager->reward($companyCustomer->id);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'       => $companyCustomer->id,
            'discount' => $discount
        ]);
    }

    public function testRewardWithCategory()
    {
        /** @var CompanyCustomer $companyCustomer */
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, [
            'discount' => 0
        ]);

        $discount = 10;

        $category = $this->tester->getFactory()->create(CustomerCategory::class, [
            'company_id' => $companyCustomer->company_id,
            'discount'   => $discount
        ]);

        // create loyalty program to grant 10% discount if customer revenue is greater than or equals 5000
        $this->tester->getFactory()->create(CustomerLoyalty::class, [
            'company_id'  => $companyCustomer->company_id,
            'event'       => CustomerLoyalty::EVENT_MONEY,
            'mode'        => CustomerLoyalty::MODE_ADD_CATEGORY,
            'amount'      => 5000,
            'category_id' => $category->id
        ]);

        $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'status'              => OrderConstants::STATUS_FINISHED,
            'price'               => 5000
        ]);

        $this->loyaltyManager->reward($companyCustomer->id);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'       => $companyCustomer->id,
            'discount' => $discount
        ]);

        $categories = $companyCustomer->getCategories()->select('id')->column();
        verify($categories)->contains($category->id);
    }

    public function testRemoveCategory()
    {
        /** @var CompanyCustomer $companyCustomer */
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class);
        $category = $this->tester->getFactory()->create(CustomerCategory::class, [
            'company_id' => $companyCustomer->company_id,
        ]);
        $companyCustomer->link('categories', $category);

        $daysAgo = 10;
        // create loyalty program to remove category if last visit was 10 days ago
        $this->tester->getFactory()->create(CustomerLoyalty::class, [
            'company_id'  => $companyCustomer->company_id,
            'event'       => CustomerLoyalty::EVENT_DAY,
            'mode'        => CustomerLoyalty::MODE_REMOVE_CATEGORY,
            'amount'      => $daysAgo,
            'category_id' => $category->id
        ]);

        $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'status'              => OrderConstants::STATUS_FINISHED,
            'price'               => 5000,
            'datetime'            => (new \DateTime())->modify("-{$daysAgo} days")->format("Y-m-d H:i:s")
        ]);

        $this->loyaltyManager->reward($companyCustomer->id);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id' => $companyCustomer->id
        ]);

        $categories = $companyCustomer->getCategories()->select('id')->column();
        verify($categories)->notContains($category->id);
    }

    public function testRemoveDiscount()
    {
        /** @var CompanyCustomer $companyCustomer */
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, ['discount' => 10]);

        $daysAgo = 10;
        // create loyalty program to remove category if last visit was 10 days ago
        $this->tester->getFactory()->create(CustomerLoyalty::class, [
            'company_id' => $companyCustomer->company_id,
            'event'      => CustomerLoyalty::EVENT_DAY,
            'mode'       => CustomerLoyalty::MODE_REMOVE_DISCOUNT,
            'amount'     => $daysAgo
        ]);

        $this->tester->getFactory()->create(Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'status'              => OrderConstants::STATUS_FINISHED,
            'price'               => 5000,
            'datetime'            => (new \DateTime())->modify("-{$daysAgo} days")->format("Y-m-d H:i:s")
        ]);

        $this->loyaltyManager->reward($companyCustomer->id);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'       => $companyCustomer->id,
            'discount' => 0
        ]);
    }

    protected function _before()
    {
        $this->loyaltyManager = \Yii::createObject(LoyaltyManager::class);
    }

    protected function _after()
    {
    }
}