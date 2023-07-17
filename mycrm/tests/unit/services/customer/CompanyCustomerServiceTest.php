<?php

namespace services\customer;


use core\helpers\company\CashbackHelper;
use core\helpers\company\PaymentHelper;
use core\helpers\GenderHelper;
use core\helpers\order\OrderConstants;
use core\models\company\Cashback;
use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerCategory;
use core\models\customer\CustomerSource;
use core\models\division\Division;
use core\models\document\Document;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use core\models\InsuranceCompany;
use core\models\order\Order;
use core\models\order\OrderService;
use core\models\user\User;
use core\models\warehouse\Usage;
use core\services\customer\CompanyCustomerService;
use core\services\dto\CustomerData;
use core\services\dto\CustomerInsuranceData;

class CompanyCustomerServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var CompanyCustomerService
     */
    private $service;

    /**
     * @var Company
     */
    private $company;

    /**
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function testCreateCustomer()
    {
        $source = $this->tester->getFactory()->create(CustomerSource::class, [
            'company_id' => $this->company->id
        ]);
        $insuranceCompany = $this->tester->getFactory()->create(InsuranceCompany::class);
        $categories = $this->tester->getFactory()->seed(2, CustomerCategory::class,
            ['company_id' => $this->company->id]
        );

        $customerData = new CustomerData(
            null,
            $this->tester->getFaker()->name,
            $this->tester->getFaker()->lastName,
            $this->tester->getFaker()->lastName,
            $this->tester->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
            $source->id,
            $this->tester->getFaker()->word
        );
        $insuranceData = new CustomerInsuranceData(
            $insuranceCompany->id,
            new \DateTime(),
            $this->tester->getFaker()->numerify('##############'),
            $this->tester->getFaker()->name
        );

        $birthDate = date("Y-m-d");
        $gender = $this->tester->getFaker()->randomElement(array_keys(GenderHelper::getGenders()));
        $email = $this->tester->getFaker()->email;
        $iin = $this->tester->getFaker()->regexify('[0-9]{12}');
        $id_card_number = $this->tester->getFaker()->regexify('[0-9]{9}');

        $address = $this->tester->getFaker()->address;
        $balance = $this->tester->getFaker()->randomNumber(3);
        $city = $this->tester->getFaker()->city;
        $comments = $this->tester->getFaker()->text(20);
        $discount = rand(1, 100);
        $employer = $this->tester->getFaker()->userName;
        $expectedCategories = [
            $categories[0]->id,
            $categories[1]->id,
        ];
        $job = $this->tester->getFaker()->jobTitle;
        $sms_birthday = false;
        $sms_exclude = false;
        $cashback_percent = rand(0, 100);

        $maxDiscount = max($discount, $categories[0]->discount, $categories[1]->discount);

        $companyCustomer = $this->service->createCustomer(
            $customerData,
            $email,
            $gender,
            $birthDate,
            $address,
            $city,
            $expectedCategories,
            $comments,
            $sms_birthday,
            $sms_exclude,
            $balance,
            $this->company->id,
            $employer,
            $job,
            $iin,
            $id_card_number,
            null,
            $discount,
            $cashback_percent,
            $insuranceData
        );

        verify($companyCustomer)->isInstanceOf(CompanyCustomer::class);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'                      => $companyCustomer->id,
            'company_id'              => $this->company->id,
            'address'                 => $address,
            'comments'                => $comments,
            'city'                    => $city,
            'sms_birthday'            => $sms_birthday,
            'balance'                 => $balance,
            'discount'                => $maxDiscount,
            'employer'                => $employer,
            'job'                     => $job,
            'source_id'               => $source->id,
            'insurance_company_id'    => $insuranceData->insurance_company_id,
            'insurer'                 => $insuranceData->insurer,
            'insurance_expire_date'   => $insuranceData->getInsuranceExpireDate(),
            'insurance_policy_number' => $insuranceData->insurance_policy_number,
            'medical_record_id'       => $customerData->medical_record_id
        ]);

        $this->tester->canSeeRecord(Customer::class, [
            'id'             => $companyCustomer->customer_id,
            'name'           => $customerData->name,
            'lastname'       => $customerData->surname,
            'patronymic'     => $customerData->patronymic,
            'phone'          => $customerData->phone,
            'iin'            => $iin,
            'id_card_number' => $id_card_number,
            'birth_date'     => $birthDate
        ]);

        $categoryIds = $companyCustomer->getCategories()->select('id')->column();
        verify($categoryIds)->equals($expectedCategories);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateProfile()
    {
        $source = $this->tester->getFactory()->create(CustomerSource::class, [
            'company_id' => $this->company->id
        ]);
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $this->company->id
        ]);
        $categories = $this->tester->getFactory()->seed(2, CustomerCategory::class,
            ['company_id' => $this->company->id]
        );
        $insuranceCompany = $this->tester->getFactory()->create(InsuranceCompany::class);

        $customerData = new CustomerData(
            $companyCustomer->id,
            $this->tester->getFaker()->name,
            $this->tester->getFaker()->lastName,
            $this->tester->getFaker()->lastName,
            $this->tester->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
            $source->id
        );
        $insuranceData = new CustomerInsuranceData(
            $insuranceCompany->id,
            new \DateTime(),
            $this->tester->getFaker()->numerify('##############'),
            $this->tester->getFaker()->name
        );

        $birthDate = date("Y-m-d");
        $gender = $this->tester->getFaker()->randomElement(array_keys(GenderHelper::getGenders()));
        $email = $this->tester->getFaker()->email;
        $iin = $this->tester->getFaker()->regexify('[0-9]{12}');
        $id_card_number = $this->tester->getFaker()->regexify('[0-9]{9}');

        $address = $this->tester->getFaker()->address;
        $balance = $this->tester->getFaker()->randomNumber(3);
        $city = $this->tester->getFaker()->city;
        $comments = $this->tester->getFaker()->text(20);
        $discount = rand(1, 100);
        $employer = $this->tester->getFaker()->userName;
        $expectedCategories = [
            $categories[0]->id,
            $categories[1]->id,
        ];
        $job = $this->tester->getFaker()->jobTitle;
        $sms_birthday = false;
        $sms_exclude = false;
        $cashback_percent = rand(0, 100);

        $maxDiscount = max($discount, $categories[0]->discount, $categories[1]->discount);

        $companyCustomer = $this->service->updateProfile(
            $customerData,
            $email,
            $gender,
            $birthDate,
            $address,
            $city,
            $expectedCategories,
            $comments,
            $sms_birthday,
            $sms_exclude,
            $balance,
            $employer,
            $job,
            $iin,
            $id_card_number,
            null,
            $discount,
            $cashback_percent,
            $insuranceData
        );

        verify($companyCustomer)->isInstanceOf(CompanyCustomer::class);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'                      => $companyCustomer->id,
            'company_id'              => $this->company->id,
            'address'                 => $address,
            'comments'                => $comments,
            'city'                    => $city,
            'sms_birthday'            => $sms_birthday,
            'balance'                 => $balance,
            'discount'                => $maxDiscount,
            'employer'                => $employer,
            'job'                     => $job,
            'source_id'               => $source->id,
            'insurance_company_id'    => $insuranceData->insurance_company_id,
            'insurer'                 => $insuranceData->insurer,
            'insurance_expire_date'   => $insuranceData->getInsuranceExpireDate(),
            'insurance_policy_number' => $insuranceData->insurance_policy_number,
        ]);

        $this->tester->canSeeRecord(Customer::class, [
            'id'             => $companyCustomer->customer_id,
            'name'           => $customerData->name,
            'lastname'       => $customerData->surname,
            'patronymic'     => $customerData->patronymic,
            'phone'          => $customerData->phone,
            'iin'            => $iin,
            'id_card_number' => $id_card_number,
            'birth_date'     => $birthDate
        ]);

        $categoryIds = $companyCustomer->getCategories()->select('id')->column();
        verify($categoryIds)->equals($expectedCategories);
    }

    public function testAddCategories()
    {
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class);
        $oldCategories = $this->tester->getFactory()->seed(2, CustomerCategory::class, [
            'company_id' => $companyCustomer->company_id
        ]);
        $companyCustomer->link('categories', $oldCategories[0]);
        $companyCustomer->link('categories', $oldCategories[1]);

        $newCategories = $this->tester->getFactory()->seed(2, CustomerCategory::class, [
            'company_id' => $companyCustomer->company_id
        ]);
        $this->service->addCategories($companyCustomer->id, [$newCategories[0]->id, $newCategories[1]->id]);

        $expectedCategories = [
            $oldCategories[0]->id,
            $oldCategories[1]->id,
            $newCategories[0]->id,
            $newCategories[1]->id,
        ];
        $maxDiscount = max(
            $companyCustomer->discount,
            $oldCategories[0]->discount,
            $oldCategories[1]->discount,
            $newCategories[0]->discount,
            $newCategories[1]->discount
        );

        $categoryIds = $companyCustomer->getCategories()->select('id')->column();
        verify($categoryIds)->equals($expectedCategories);

        // check if maximum discount of categories was assigned to customer
        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'       => $companyCustomer->id,
            'discount' => $maxDiscount
        ]);
    }

    /**
     * @group debt
     */
    public function testPayDebt()
    {
        $debt = -10000;
        $amount = 5000;
        $user = $this->tester->getFactory()->create(User::class, ['company_id' => $this->company->id]);
        \Yii::$app->set('user', $user);
        $companyCustomer = $this->tester->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $this->company->id,
            'balance'    => $debt
        ]);
        $division = $this->tester->getFactory()->create(Division::class, ['company_id' => $this->company->id]);
        $companyCash = $this->tester->getFactory()->create(CompanyCash::class, ['division_id' => $division->id]);
        $costItem = CompanyCostItem::find()->company($this->company->id)->isDebtPayment()->one();

        $orders = $this->tester->getFactory()->seed(2, Order::class, [
            'company_customer_id' => $companyCustomer->id,
            'payment_difference'  => $debt / 2,
            'division_id'         => $division->id,
            'company_cash_id'     => $companyCash->id,
            'status'              => OrderConstants::STATUS_FINISHED
        ]);

        foreach ($orders as $order) {
            $this->tester->getFactory()->create(OrderService::class, [
                'order_id' => $order->id,
                'price'    => $amount,
                'discount' => 0
            ]);
        }

        $this->service->payDebt($companyCustomer->id, [PaymentHelper::CASH_ID => $amount], $user->id, new \DateTime());

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'      => $companyCustomer->id,
            'balance' => $debt + $amount
        ]);

        $this->tester->canSeeRecord(CompanyCashflow::class, [
            'cash_id'       => $companyCash->id,
            'cost_item_id'  => $costItem->id,
            'division_id'   => $division->id,
            'customer_id'   => $companyCustomer->id,
            'receiver_mode' => CompanyCashflow::RECEIVER_STAFF,
            'value'         => $amount,
            'user_id'       => $user->id
        ]);

        $this->tester->canSeeRecord(Order::class, [
            'payment_difference' => $debt / 2 + $amount
        ]);
    }

    public function testMerge()
    {
        $user = $this->tester->getFactory()->create(User::class, ['company_id' => $this->company->id]);

        \Yii::$app->set('user', $user);

        $companyCustomers = $this->tester->getFactory()->seed(3, CompanyCustomer::class, [
            'company_id'       => $this->company->id,
            'balance'          => 5000,
            'cashback_balance' => 0
        ]);

        $fOrder = $this->tester->getFactory()->create(Order::class,
            ['company_customer_id' => $companyCustomers[1]->id]);
        $sOrder = $this->tester->getFactory()->create(Order::class,
            ['company_customer_id' => $companyCustomers[2]->id]);

        $fCashback = $this->tester->getFactory()->create(Cashback::class,
            ['company_customer_id' => $companyCustomers[1]->id, 'type' => CashbackHelper::TYPE_IN, 'amount' => 300]);
        $sCashback = $this->tester->getFactory()->create(Cashback::class,
            ['company_customer_id' => $companyCustomers[2]->id, 'type' => CashbackHelper::TYPE_IN, 'amount' => 300]);

        $fDoc = $this->tester->getFactory()->create(Document::class,
            ['company_customer_id' => $companyCustomers[1]->id]);
        $sDoc = $this->tester->getFactory()->create(Document::class,
            ['company_customer_id' => $companyCustomers[2]->id]);

        $fCashflow = $this->tester->getFactory()->create(CompanyCashflow::class,
            ['customer_id' => $companyCustomers[1]->id, 'company_id' => $this->company->id]);
        $sCashflow = $this->tester->getFactory()->create(CompanyCashflow::class,
            ['customer_id' => $companyCustomers[2]->id, 'company_id' => $this->company->id]);

        $fUsage = $this->tester->getFactory()->create(Usage::class,
            ['company_customer_id' => $companyCustomers[1]->id, 'company_id' => $this->company->id]);
        $sUsage = $this->tester->getFactory()->create(Usage::class,
            ['company_customer_id' => $companyCustomers[2]->id, 'company_id' => $this->company->id]);

        $this->service->merge($companyCustomers[0]->id, [$companyCustomers[1]->id, $companyCustomers[2]->id]);

        $this->tester->canSeeRecord(CompanyCustomer::class, [
            'id'               => $companyCustomers[0]->id,
            'balance'          => 5000 * 3,
            'cashback_balance' => 300 * 2
        ]);
        $this->tester->canSeeRecord(CompanyCustomer::class, ['id' => $companyCustomers[1]->id, 'is_active' => false]);
        $this->tester->canSeeRecord(CompanyCustomer::class, ['id' => $companyCustomers[2]->id, 'is_active' => false]);

        $this->tester->canSeeRecord(Order::class,
            ['id' => $fOrder->id, 'company_customer_id' => $companyCustomers[0]->id]);
        $this->tester->canSeeRecord(Order::class,
            ['id' => $sOrder->id, 'company_customer_id' => $companyCustomers[0]->id]);

        $this->tester->canSeeRecord(Cashback::class,
            ['id' => $fCashback->id, 'company_customer_id' => $companyCustomers[0]->id]);
        $this->tester->canSeeRecord(Cashback::class,
            ['id' => $sCashback->id, 'company_customer_id' => $companyCustomers[0]->id]);

        $this->tester->canSeeRecord(CompanyCashflow::class,
            ['id' => $fCashflow->id, 'customer_id' => $companyCustomers[0]->id]);
        $this->tester->canSeeRecord(CompanyCashflow::class,
            ['id' => $sCashflow->id, 'customer_id' => $companyCustomers[0]->id]);

        $this->tester->canSeeRecord(Document::class,
            ['id' => $fDoc->id, 'company_customer_id' => $companyCustomers[0]->id]);
        $this->tester->canSeeRecord(Document::class,
            ['id' => $sDoc->id, 'company_customer_id' => $companyCustomers[0]->id]);

        $this->tester->canSeeRecord(Usage::class,
            ['id' => $fUsage->id, 'company_customer_id' => $companyCustomers[0]->id]);
        $this->tester->canSeeRecord(Usage::class,
            ['id' => $sUsage->id, 'company_customer_id' => $companyCustomers[0]->id]);
    }

    protected function _before()
    {
        if (!$this->company) {
            $this->company = $this->tester->getFactory()->create(Company::class);
        }

        $this->service = \Yii::createObject(CompanyCustomerService::class);
    }

    protected function _after()
    {
    }

}