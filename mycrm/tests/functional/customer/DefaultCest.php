<?php

namespace tests\functional\customer;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use core\models\customer\Customer;
use core\models\customer\CustomerCategory;
use core\models\division\Division;
use core\models\order\Order;
use FunctionalTester;

class DefaultCest
{
    private $responseFormat
        = [
            'id'                 => 'integer',
            'name'               => 'string',
            'lastname'           => 'string',
            'patronymic'         => 'string',
            'fullname'           => 'string',
            'phone'              => 'string',
            'email'              => 'string:email|null',
            'birth_date'         => 'string:date|null',
            'gender'             => 'integer|null',
            'gender_title'       => 'string',
            'iin'                => 'string|null',
            'id_card_number'     => 'string|null',
            'address'            => 'string|null',
            'balance'            => 'integer',
            'canceledOrders'     => 'integer',
            'categories'         => 'array',
            'city'               => 'array|null',
            'comments'           => 'array|null',
            'debt'               => 'integer',
            'deposit'            => 'integer',
            'discount'           => 'integer',
            'employer'           => 'array|null',
            'finishedOrders'     => 'integer',
            'job'                => 'array|null',
            'revenue'            => 'integer',
            'sms_birthday'       => 'boolean',
            'sms_birthday_title' => 'string',
            'sms_exclude'        => 'boolean',
            'sms_exclude_title'  => 'string',
            'source_id'          => 'integer|null',
            'medical_record_id'  => 'string|null',
        ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function index(FunctionalTester $I)
    {
        // I can't see CompanyCustomer without authorization
        $I->wantToTest('Customer index');
        $I->sendGET('customer');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        // I seed "my" CompanyCustomers
        /** @var CompanyCustomer[] $myCompanyCustomers */
        $myCompanyCustomers = $I->getFactory()->seed(3, CompanyCustomer::class, [
            'company_id' => $user->company_id,
        ]);

        /** @var CompanyCustomer[] $foreignCompanyCustomers */
        // I seed "foreign" CompanyCustomers
        $foreignCompanyCustomers = $I->getFactory()->seed(3, CompanyCustomer::class, [
            // random Companies will be generated
        ]);

        $I->sendGET('customer?expand=canceledOrders,categories,debt,deposit,revenue,finishedOrders');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        // I see my CompanyCustomers
        foreach ($myCompanyCustomers as $myCompanyCustomer) {
            $I->seeResponseContainsJson(['id' => $myCompanyCustomer->id]);
        }

        // I don't see foreign CompanyCustomers
        foreach ($foreignCompanyCustomers as $foreignCompanyCustomer) {
            $I->dontSeeResponseContainsJson(['id' => $foreignCompanyCustomer->id]);
        }

        /** @var Customer $customer */
        $customer = $I->getFactory()->create(Customer::class, [
            'iin' => '886644123321',
            'id_card_number' => $I->getFaker()->numberBetween(100000000000, 999999999999)
        ]);
        /** @var CompanyCustomer $companyCustomer */
        $companyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'customer_id' => $customer->id,
            'company_id'  => $user->company_id,
        ]);

        // (DRAFT) I test search
        $I->sendGET("customer?term={$customer->name}&expand=canceledOrders,categories,debt,deposit,revenue,finishedOrders");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer?term={$customer->iin}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer?term={$customer->id_card_number}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer?iin={$customer->iin}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer?id_card_number={$customer->id_card_number}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer?name={$customer->name}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer", ['phone' => $customer->phone]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer?lastname={$customer->lastname}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer?patronymic={$customer->patronymic}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);

        $I->sendGET("customer?email={$customer->email}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => $companyCustomer->id]);
    }

    public function view(FunctionalTester $I)
    {
        // I can't see CompanyCustomer without authorization
        $I->wantToTest('Customer view');
        $I->sendGET('customer/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $myCompanyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id,
        ]);

        // I see my CompanyCustomers
        $I->sendGET("customer/{$myCompanyCustomer->id}?expand=canceledOrders,categories,debt,deposit,revenue,finishedOrders");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);

        $foreignCompanyCustomer = $I->getFactory()->create(CompanyCustomer::class, []);

        // I can't see foreign CompanyCustomers
        $I->sendGET("customer/{$foreignCompanyCustomer->id}");
        $I->seeResponseCodeIs(403);

        // TODO Should I be able to view "deleted" (is_active = false) CompanyCustomers?
    }

    public function export(FunctionalTester $I)
    {
        // I can't export CompanyCustomer without authorization
        $I->wantToTest('Customer export');
        $I->sendPOST('customer/export');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        // I seed "my" CompanyCustomers
        $myCompanyCustomer = $I->getFactory()->seed(3, CompanyCustomer::class, [
            'company_id' => $user->company_id,
        ]);

        // I seed "foreign" CompanyCustomers
        $foreignCompanyCustomer = $I->getFactory()->seed(3, CompanyCustomer::class, []);

        $I->sendPOST('customer/export');
        $I->haveHttpHeader('Access-Control-Allow-Origin', '*');
        $I->seeResponseCodeIs(200);
    }

    public function import(FunctionalTester $I)
    {
        // I can't import CompanyCustomer without authorization
        $I->wantToTest('Customer import');
        $I->sendPOST('customer/import');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        // I send POST import with attached file
        $I->sendPOST('customer/import', ['inline' => 0], [
            'excelFile' => codecept_data_dir('company_customer_import.xls')
        ]);
        $I->seeResponseCodeIs(200);
    }

    public function deleteMultiple(FunctionalTester $I)
    {
        // I can't delete multiple CompanyCustomer without authorization
        $I->wantToTest('Customer delete-multiple');
        $I->sendPOST('customer/multiple/delete');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $I->assignRoles($user, 'company');

        // I seed "my" CompanyCustomers
        /** @var CompanyCustomer[] $myCompanyCustomers */
        $myCompanyCustomers = $I->getFactory()->seed(3, CompanyCustomer::class, [
            'company_id' => $user->company_id,
        ]);
        /** @var CompanyCustomer[] $myLastCompanyCustomers */
        $myLastCompanyCustomers = $I->getFactory()->seed(2, CompanyCustomer::class, [
            'company_id' => $user->company_id,
        ]);

        // I seed "foreign" CompanyCustomers
        /** @var CompanyCustomer[] $foreignCompanyCustomers */
        $foreignCompanyCustomers = $I->getFactory()->seed(3, CompanyCustomer::class, []);

        $ids = array_map(function (CompanyCustomer $model) {
            return $model->id;
        }, $myCompanyCustomers);

        $foreignIds = array_map(function (CompanyCustomer $model) {
            return $model->id;
        }, $foreignCompanyCustomers);

        // I send to delete "my" CompanyCustomers and "foreign" CompanyCustomers
        $I->sendPOST('customer/multiple/delete', [
            'ids' => $ids + $foreignIds
        ]);
        $I->seeResponseCodeIs(200);

        // Check CompanyCustomers
        // My "deleted" CompanyCustomers
        foreach($myCompanyCustomers as $model) {
            $model->refresh();

            $I->seeRecord(CompanyCustomer::className(), [
                'id' => $model->id,
                'is_active' => false,
            ]);
        }

        // My remained CompanyCustomers
        foreach($myLastCompanyCustomers as $model) {
            $model->refresh();

            $I->seeRecord(CompanyCustomer::className(), [
                'id' => $model->id,
                'is_active' => true,
            ]);
        }

        // Foreign CompanyCustomers that I targeted should not be "deleted"
        foreach($foreignCompanyCustomers as $model) {
            $model->refresh();

            $I->seeRecord(CompanyCustomer::className(), [
                'id' => $model->id,
                'is_active' => true,
            ]);
        }
    }

    public function sendRequestMultiple(FunctionalTester $I)
    {
        $I->wantToTest('Customer send-request-multiple');

        // I can't delete multiple CompanyCustomer without authorization
        $I->sendPOST('customer/multiple/send-request');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        /** @var CompanyCustomer[] $myCompanyCustomers */
        $myCompanyCustomers = $I->getFactory()->seed(3, CompanyCustomer::class, ['company_id' => $user->company_id]);

        /** @var CompanyCustomer[] $foreignCompanyCustomers */
        $foreignCompanyCustomers = $I->getFactory()->seed(3, CompanyCustomer::class);

        // TODO кажется заглушки в SMS нет
//        $I->sendPOST('customer/multiple/send-request', [
//            'ids' => array_map(function (CompanyCustomer $model) {
//                return $model->id;
//            }, $myCompanyCustomers),
//            'message' => 'Hello World',
//        ]);
//        $I->seeResponseCodeIs(200);
//        $I->haveHttpHeader('Access-Control-Allow-Origin', '*');
    }

    public function addCategoriesMultiple(FunctionalTester $I)
    {
        $I->wantToTest('Customer add-categories-multiple');

        $I->sendPOST('customer/multiple/add-categories');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        /** @var CompanyCustomer[] $myCompanyCustomers */
        $myCompanyCustomers = $I->getFactory()->seed(3, CompanyCustomer::class, ['company_id' => $user->company_id]);

        /** @var CustomerCategory[] $customerCategories */
        $customerCategories = $I->getFactory()->seed(3, CustomerCategory::class, ['company_id' => $user->company_id]);

        $I->sendPOST('customer/multiple/add-categories', [
            'ids' => array_map(function (CompanyCustomer $model) {
                return $model->id;
            }, $myCompanyCustomers),
            'category_ids' => array_map(function (CustomerCategory $model) {
                return $model->id;
            }, $customerCategories),
        ]);
        $I->seeResponseCodeIs(200);
        $I->haveHttpHeader('Access-Control-Allow-Origin', '*');
    }


    /**
     * @group debug
     */
    public function update(FunctionalTester $I)
    {
        // I can't update CompanyCustomer without authorization
        $I->wantToTest('Customer update');
        $I->sendPUT("customer/1");
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $I->assignRoles($user, 'company');

        $I->sendPUT("customer/1");
        $I->seeResponseCodeIs(404);

        /** @var CompanyCustomer $myCompanyCustomer */
        $myCompanyCustomer = $I->getFactory()->create(CompanyCustomer::class, [
            'company_id' => $user->company_id,
        ]);

        /** @var CompanyCustomer $foreignCompanyCustomer */
        $foreignCompanyCustomer = $I->getFactory()->create(CompanyCustomer::class, []);

        // Update my Customer
        $I->sendPUT("customer/{$myCompanyCustomer->id}?expand=canceledOrders,categories,debt,deposit,revenue,finishedOrders", [
            'name' => 'Блабла',
            'lastname' => 'Блаблаев',
            'address' => 'Блаблаева 75',
            'phones' => [
                ['value' => '+7 701 381 71 15'],
                ['value' => '+7 701 381 71 51'],
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
        $I->seeRecord(CompanyCustomer::class, [
            'id' => $myCompanyCustomer->id,
            'address' => 'Блаблаева 75',
            'customer_id' => $myCompanyCustomer->customer_id
        ]);
        $I->seeRecord(Customer::class, [
            'name' => 'Блабла',
            'lastname' => 'Блаблаев',
        ]);

        // Update foreign Customer
        $I->sendPUT("customer/{$foreignCompanyCustomer->id}", [
            'id' => $foreignCompanyCustomer->id,
            'name' => 'Чужой',
            'address' => 'улица Вязов',
        ]);
        $I->seeResponseCodeIs(404);
    }

    public function lost(FunctionalTester $I)
    {
        $I->wantToTest('Customer lost list');
        $I->sendGET('customer/lost');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $division = $I->getFactory()->create( Division::class, [
            'company_id' => $user->company_id,
        ]);

        $customer = $I->getFactory()->create(Customer::class);
        $companyCustomer = $I->getFactory()->create( CompanyCustomer::class, [
            'company_id' => $user->company_id,
            'customer_id' => $customer->id
        ]);

        //  check lost
        $I->getFactory()->create(Order::class, [
            'division_id' => $division->id,
            'datetime' => date("Y-m-d", strtotime("-30 days")),
            'status' => OrderConstants::STATUS_FINISHED,
            'created_user_id' => $user->id,
            'company_customer_id' => $companyCustomer->id
        ]);

        $I->sendGET("customer/lost?expand=canceledOrders,categories,debt,deposit,revenue,finishedOrders");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        //  check not lost
        $I->getFactory()->create(Order::class, [
            'division_id' => $division->id,
            'datetime' => date("Y-m-d", strtotime("-10 days")),
            'status' => OrderConstants::STATUS_FINISHED,
            'created_user_id' => $user->id,
            'company_customer_id' => $companyCustomer->id
        ]);
        $I->sendGET("customer/lost");
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('[]');
    }

    public function history(FunctionalTester $I)
    {
        $I->wantToTest('Customer history list');
        $I->sendGET('customer/123/history');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $I->assignRoles($user, 'company');

        $customer = $I->getFactory()->create(Customer::class);
        $myCompanyCustomer = $I->getFactory()->create( CompanyCustomer::class, [
            'company_id' => $user->company_id,
            'customer_id' => $customer->id
        ]);

        $otherCompanyCustomer = $I->getFactory()->create( CompanyCustomer::class, [
            'customer_id' => $customer->id
        ]);

        // I open other CompanyCustomer History
        $I->sendGET("customer/{$otherCompanyCustomer->id}/history");
        $I->seeResponseCodeIs(404);

        // I open my CompanyCustomer History
        $I->sendGET("customer/{$myCompanyCustomer->id}/history");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['action' => 'Создан']);
        $I->dontSeeResponseContainsJson(['action' => 'Изменен']);

        sleep(1);
        // I edit my CompanyCustomer in order to create History
        $I->sendPUT("customer/{$myCompanyCustomer->id}?expand=canceledOrders,categories,debt,deposit,revenue,finishedOrders", [
            'name' => 'Блабла',
            'lastname' => 'Блаблаев',
            'address' => 'Блаблаева 75',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);

        // I check newly created history
        $I->sendGET("customer/{$myCompanyCustomer->id}/history");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['action' => 'Изменен']);
        $I->seeResponseContainsJson(['name' => 'Блабла']);
    }

    public function merge(FunctionalTester $I)
    {
        // I can't see CompanyCustomer without authorization
        $I->wantToTest('Customer merge');
        $I->sendPOST('customer/1/merge');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $foreignCompanyCustomer = $I->getFactory()->create(CompanyCustomer::class, []);

        // I can't see foreign CompanyCustomers
        $I->sendPOST("customer/{$foreignCompanyCustomer->id}/merge");
        $I->seeResponseCodeIs(404);

        $companyCustomers = $I->getFactory()->seed(3, CompanyCustomer::class, [
            'company_id' => $user->company_id
        ]);

        $I->sendPOST("customer/{$companyCustomers[0]->id}/merge?expand=canceledOrders,categories,debt,deposit,revenue,finishedOrders");
        $I->seeResponseCodeIs(422);

        $I->sendPOST("customer/{$companyCustomers[0]->id}/merge?expand=canceledOrders,categories,debt,deposit,revenue,finishedOrders",
            [
                'customer_ids' => [$companyCustomers[1]->id, $companyCustomers[2]->id]
            ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
