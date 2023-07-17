<?php

namespace tests\unit\services\company;

use core\helpers\company\PaymentHelper;
use core\helpers\CompanyHelper;
use core\helpers\finance\CompanyCostItemHelper;
use core\models\company\Company;
use core\models\company\CompanyPosition;
use core\models\company\Tariff;
use core\models\company\TariffPayment;
use core\models\CompanyPaymentLog;
use core\models\document\DocumentForm;
use core\models\document\DocumentFormCompanyPositionMap;
use core\models\document\DocumentFormPositionMap;
use core\models\finance\CompanyCostItem;
use core\models\Position;
use core\models\ServiceCategory;
use core\models\user\User;
use core\models\webcall\WebCall;
use core\services\company\CompanyService;
use core\services\dto\CompanyDetailsData;
use core\services\dto\CompanyPaymentData;
use core\services\dto\PersonData;

class CompanyTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var CompanyService
     */
    private $service;

    /**
     * @var Company
     */
    private $company;

    /**
     * @throws \Exception
     */
    public function testAdd()
    {
        /** @var ServiceCategory $category */
        $category = $this->tester->getFactory()->create(ServiceCategory::class);

        $positions = $this->tester->getFactory()->seed(5,Position::class, [
            'service_category_id' => $category->id
        ]);

        $documentForms = $this->tester->getFactory()->seed(5,DocumentForm::class);

        foreach ($positions as $position) {
            foreach ($documentForms as $documentForm) {
                $this->tester->getFactory()->create(DocumentFormPositionMap::class, [
                    'document_form_id'  => $documentForm->id,
                    'position_id'       => $position->id
                ]);
            }
        }

        $companyDetailsData = new CompanyDetailsData(
            'address',
            'bank',
            'bik',
            'bin',
            'iik',
            $this->tester->getFaker()->date(),
            'license_number',
            'name',
            'phone'
        );

        $personData = new PersonData(
            $this->tester->getFaker()->firstName,
            $this->tester->getFaker()->lastName,
            'patronymic'
        );

        /** @var Tariff $tariff */
        $tariff = $this->tester->getFactory()->create(Tariff::class);

        $companyPaymentData = new CompanyPaymentData($tariff->id);

        $status = $this->tester->getFaker()->randomElement(array_keys(CompanyHelper::getStatuses()));
        $publish = $this->tester->getFaker()->randomElement(array_keys(CompanyHelper::getPublishStatuses()));
        $web_call_access = $this->tester->getFaker()->randomElement(array_keys(CompanyHelper::getWebCallStatus()));
        $file_manager_enabled = $this->tester->getFaker()->boolean();
        $show_referrer = $this->tester->getFaker()->boolean();
        $interval = $this->tester->getFaker()->randomNumber(2);
        $show_new_interface = $this->tester->getFaker()->boolean();
        $unlimited_sms = $this->tester->getFaker()->boolean();
        $notify_about_order = $this->tester->getFaker()->boolean();
        $limit_auth_time_by_schedule = $this->tester->getFaker()->boolean();

        $company = $this->service->add(
            $status,
            $publish,
            $category->id,
            $web_call_access,
            $file_manager_enabled,
            $show_referrer,
            $interval,
            $show_new_interface,
            $companyDetailsData,
            $personData,
            $companyPaymentData,
            $unlimited_sms,
            $notify_about_order,
            $limit_auth_time_by_schedule
        );

        verify($company)->isInstanceOf(Company::class);

        $this->tester->canSeeRecord(Company::class, [
            'id' => $company->id,
            'name' => $companyDetailsData->name,
//            'logo_id' => $companyDetailsData->logo_id,
            'status' => $status,
            'head_name' => $personData->name,
            'head_surname' => $personData->surname,
            'head_patronymic' => $personData->patronymic,
            'category_id' => $category->id,
            'publish' => $publish,
            'address' => $companyDetailsData->address,
            'iik' => $companyDetailsData->iik,
            'bank' => $companyDetailsData->bank,
            'bin' => $companyDetailsData->bin,
            'bik' => $companyDetailsData->bik,
            'phone' => $companyDetailsData->phone,
            'license_issued' => $companyDetailsData->license_issued,
            'license_number' => $companyDetailsData->license_number,
//            'widget_prefix' => $companyDetailsData->widget_prefix,
            'file_manager_enabled' => $file_manager_enabled,
            'show_referrer' => $show_referrer,
            'interval' => $interval,
//            'online_start' => $companyDetailsData->online_start,
//            'online_finish' => $companyDetailsData->online_finish,
            'show_new_interface' => $show_new_interface,
            'unlimited_sms' => $unlimited_sms,
            'tariff_id' => $tariff->id,
            'notify_about_order' => $notify_about_order,
        ]);

        $this->tester->canSeeRecord(WebCall::class, [
            'company_id' => $company->id,
            'enabled' => $web_call_access
        ]);

        $this->tester->wantToTest("check whether the companyPositions are generated");
        foreach ($positions as $position) {
            $this->tester->canSeeRecord(CompanyPosition::class, [
                'company_id' => $company->id,
                'name' => $position->name,
                'position_id' => $position->id
            ]);
        }

        $this->tester->wantToTest("check whether the documents are attached to positions");
        foreach ($documentForms as $documentForm) {
            $this->tester->canSeeRecord(DocumentFormCompanyPositionMap::class, [
                'document_form_id' => $documentForm->id
            ]);
        }

        foreach (CompanyCostItemHelper::getInitialItems() as $item) {
            $this->tester->canSeeRecord(CompanyCostItem::class, [
                'company_id' => $company->id,
                'name' => $item['name'],
                'type' => $item['type'],
                'comments' => null,
                'cost_item_type' => $item['cost_item_type'],
            ]);
        }
    }

    /**
     * @throws \Exception
     */
    public function testEdit()
    {
        /** @var ServiceCategory $category */
        $category = $this->tester->getFactory()->create(ServiceCategory::class);

        $status = $this->tester->getFaker()->randomElement(array_keys(CompanyHelper::getStatuses()));
        $publish = $this->tester->getFaker()->randomElement(array_keys(CompanyHelper::getPublishStatuses()));
        $web_call_access = $this->tester->getFaker()->randomElement(array_keys(CompanyHelper::getWebCallStatus()));
        $file_manager_enabled = $this->tester->getFaker()->boolean();
        $show_referrer = $this->tester->getFaker()->boolean();
        $interval = $this->tester->getFaker()->randomNumber(2);
        $show_new_interface = $this->tester->getFaker()->boolean();
        $unlimited_sms = $this->tester->getFaker()->boolean();
        $notify_about_order = $this->tester->getFaker()->boolean();
        $limit_auth_time_by_schedule = $this->tester->getFaker()->boolean();

        $companyDetailsData = new CompanyDetailsData(
            'address2',
            'bank2',
            'bik2',
            'bin2',
            'iik2',
            $this->tester->getFaker()->date(),
            'license_number2',
            'name2',
            'phone2'
        );

        $personData = new PersonData(
            $this->tester->getFaker()->firstName,
            $this->tester->getFaker()->lastName,
            'patronymic'
        );

        /** @var Tariff $tariff */
        $tariff = $this->tester->getFactory()->create(Tariff::class);

        $companyPaymentData = new CompanyPaymentData($tariff->id);

        $company = $this->service->edit(
            $this->company->id,
            $status,
            $publish,
            $category->id,
            $web_call_access,
            $file_manager_enabled,
            $show_referrer,
            $interval,
            $show_new_interface,
            $companyDetailsData,
            $personData,
            $companyPaymentData,
            $unlimited_sms,
            $notify_about_order,
            $limit_auth_time_by_schedule
        );

        verify($company)->isInstanceOf(Company::class);

        $this->tester->canSeeRecord(Company::class, [
            'id' => $company->id,
            'name' => $companyDetailsData->name,
//            'logo_id' => $companyDetailsData->logo_id,
            'status' => $status,
            'head_name' => $personData->name,
            'head_surname' => $personData->surname,
            'head_patronymic' => $personData->patronymic,
            'category_id' => $category->id,
            'publish' => $publish,
            'address' => $companyDetailsData->address,
            'iik' => $companyDetailsData->iik,
            'bank' => $companyDetailsData->bank,
            'bin' => $companyDetailsData->bin,
            'bik' => $companyDetailsData->bik,
            'phone' => $companyDetailsData->phone,
            'license_issued' => $companyDetailsData->license_issued,
            'license_number' => $companyDetailsData->license_number,
//            'widget_prefix' => $companyDetailsData->widget_prefix,
            'file_manager_enabled' => $file_manager_enabled,
            'show_referrer' => $show_referrer,
            'interval' => $interval,
//            'online_start' => $companyDetailsData->online_start,
//            'online_finish' => $companyDetailsData->online_finish,
            'show_new_interface' => $show_new_interface,
            'unlimited_sms' => $unlimited_sms,
            'tariff_id' => $tariff->id,
            'notify_about_order' => $notify_about_order,
        ]);

        $this->tester->canSeeRecord(WebCall::class, [
            'company_id' => $company->id,
            'enabled' => $web_call_access
        ]);
    }

    public function testRestrictEdit()
    {
        $image_id = 2;

        $companyDetailsData = new CompanyDetailsData(
            'address3',
            'bank3',
            'bik3',
            'bin3',
            'iik3',
            $this->tester->getFaker()->date(),
            'license_number3',
            'name3',
            'phone3',
            null,
            null,
            null,
            $image_id
        );

        $personData = new PersonData(
            $this->tester->getFaker()->firstName,
            $this->tester->getFaker()->lastName,
            'patronymic'
        );

        $notify_about_order = $this->tester->getFaker()->boolean();

        $company = $this->service->restrictEdit(
            $this->company->id,
            $companyDetailsData,
            $personData,
            $notify_about_order
        );

        verify($company)->isInstanceOf(Company::class);

        $this->tester->canSeeRecord(Company::class, [
            'id' => $company->id,
            'name' => $companyDetailsData->name,
//            'logo_id' => $companyDetailsData->logo_id,
            'head_name' => $personData->name,
            'head_surname' => $personData->surname,
            'head_patronymic' => $personData->patronymic,
            'address' => $companyDetailsData->address,
            'iik' => $companyDetailsData->iik,
            'bank' => $companyDetailsData->bank,
            'bin' => $companyDetailsData->bin,
            'bik' => $companyDetailsData->bik,
            'phone' => $companyDetailsData->phone,
            'license_issued' => $companyDetailsData->license_issued,
            'license_number' => $companyDetailsData->license_number,
//            'widget_prefix' => $companyDetailsData->widget_prefix,
//            'online_start' => $companyDetailsData->online_start,
//            'online_finish' => $companyDetailsData->online_finish,
            'notify_about_order' => $notify_about_order,
            'logo_id' => $image_id,
        ]);
    }

    public function testAddPayment()
    {
        $currency = $this->tester->getFaker()->randomElement(array_keys(PaymentHelper::getCurrencyList()));
        $description = $this->tester->getFaker()->text(20);
        $message = $this->tester->getFaker()->text(21);
        $value = $this->tester->getFaker()->randomNumber(2);
        $is_confirmed = $this->tester->getFaker()->boolean();

        $paymentLog = $this->service->addPayment(
            $this->company->id,
            $currency,
            $description,
            $message,
            $value,
            $is_confirmed
        );

        verify($paymentLog)->isInstanceOf(CompanyPaymentLog::class);

        $this->tester->canSeeRecord(CompanyPaymentLog::class, [
            'id' => $paymentLog->id,
            'company_id' => $this->company->id,
            'currency' => $currency,
            'description' => $description,
            'message' => $message,
            'value' => $value,
//            'confirmed_time' 'IS NOT NULL'
        ]);
    }

    public function testPayTariff()
    {
        $sum = $this->tester->getFaker()->numberBetween(100, 1000);
        $period = $this->tester->getFaker()->randomNumber(1);
        $start_date = $this->tester->getFaker()->dateTimeBetween('-2 months', '-1 month')->format("Y-m-d");

        $this->service->payTariff($this->company->id, $sum, $period, $start_date);

        $this->tester->canSeeRecord(TariffPayment::class, [
            'sum' => $sum,
            'period' => $period,
            'start_date' => $start_date,
        ]);
    }

    public function testEditPayTariff()
    {
        /** @var TariffPayment $tariffPayment */
        $tariffPayment = $this->tester->getFactory()->create(TariffPayment::class, [
            'company_id' => $this->company->id,
        ]);

        $this->tester->canSeeRecord(TariffPayment::class, [
            'sum' => $tariffPayment->sum,
            'period' => $tariffPayment->period,
            'start_date' => $tariffPayment->start_date,
        ]);

        $sum = $this->tester->getFaker()->numberBetween(100, 1000);
        $period = $this->tester->getFaker()->randomNumber(1);
        $start_date = $this->tester->getFaker()->dateTimeBetween('-2 months', '-1 month')->format("Y-m-d");

        $this->service->editTariffPayment($tariffPayment->id, $sum, $period, $start_date);

        $this->tester->canSeeRecord(TariffPayment::class, [
            'sum' => $sum,
            'period' => $period,
            'start_date' => $start_date,
        ]);
    }

    public function testLogoutCompanyUsers()
    {
        /** @var User[] $companyUsers */
        $companyUsers = $this->tester->getFactory()->seed(3, User::class, [
            'company_id' => $this->company->id,
        ]);

        foreach ($companyUsers as $companyUser) {
            $this->tester->canSeeRecord(User::class, [
                'access_token' => $companyUser->access_token,
            ]);
        }

        $this->service->logoutCompanyUsers($this->company->id);

        foreach ($companyUsers as $companyUser) {
            $this->tester->cantSeeRecord(User::class, [
                'access_token' => $companyUser->access_token,
            ]);
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function _before()
    {
        if ( ! $this->company) {
            $this->company = $this->tester->getFactory()->create(Company::class);
        }

        $this->service = \Yii::createObject(CompanyService::class);
    }

    protected function _after()
    {
    }

}