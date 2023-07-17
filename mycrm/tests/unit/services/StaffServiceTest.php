<?php

namespace services;


use api\modules\v2\search\document\DocumentFormSearch;
use core\forms\staff\StaffCreateForm;
use core\forms\staff\StaffUpdateForm;
use core\helpers\CompanyHelper;
use core\helpers\GenderHelper;
use core\models\company\Company;
use core\models\company\CompanyPosition;
use core\models\company\Tariff;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\document\DocumentForm;
use core\models\document\DocumentFormCompanyPositionMap;
use core\models\document\DocumentFormPositionMap;
use core\models\Position;
use core\models\ServiceCategory;
use core\models\Staff;
use core\models\StaffDivisionMap;
use core\models\user\User;
use core\models\user\UserDivision;
use core\services\company\CompanyService;
use core\services\dto\CompanyDetailsData;
use core\services\dto\CompanyPaymentData;
use core\services\dto\PersonData;
use core\services\staff\StaffModelService;
use yii\helpers\ArrayHelper;

class StaffServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var StaffModelService
     */
    protected $service;

    /**
     * @var CompanyService
     */
    protected $companyService;


    public function testGlobalPositionsAssignment()
    {
        /** @var ServiceCategory $category */
        $category = $this->tester->getFactory()->create(ServiceCategory::class);

        $positions = $this->tester->getFactory()->seed(5,Position::class, [
            'service_category_id' => $category->id
        ]);

        //  this number will be checked in the end
        $documentFormsNumber = $this->tester->getFaker()->numberBetween(1, 10);
        $documentForms = $this->tester->getFactory()->seed($documentFormsNumber,DocumentForm::class);

        //  attach documentForms to each position
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
        $tariff = $this->tester->getFactory()->create(Tariff::class, ['staff_qty' => 10]);

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

        $company = $this->companyService->add(
            $status,
            $publish,
            $category->id,
            $web_call_access, $file_manager_enabled, $show_referrer, $interval, $show_new_interface,
            $companyDetailsData,
            $personData,
            $companyPaymentData,
            $unlimited_sms,
            $notify_about_order,
            $limit_auth_time_by_schedule
        );

        verify($company)->isInstanceOf(Company::class);

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


        //  now create staff to check that documents were attached to staff
        $anotherCompany = $this->tester->getFactory()->create(Company::class, ['tariff_id' => $tariff->id]);
        $division = $this->tester->getFactory()->create(Division::class, ['company_id' => $company->id]);
        $user = $this->tester->getFactory()->create(User::class, [
            'company_id' => $anotherCompany->id,
            'status'     => User::STATUS_DISABLED
        ]);
        $companyPositionIds = ArrayHelper::getColumn(
            $company->companyPositions,
            'id'
        );
        $form = new StaffCreateForm($company->id, [
            'name'                 => $this->tester->getFaker()->firstName,
            'phone'                => $user->username,
            'username'             => $user->username,
            'gender'               => GenderHelper::GENDER_UNDEFINED,
            'color'                => '',
            'create_user'          => true,
            'see_own_orders'       => false,
            'company_position_ids' => $companyPositionIds,
            'division_ids'         => [$division->id],
            'division_service_ids' => [],
            'user_divisions'       => [$division->id],
            'user_permissions'     => [],
        ]);

        $staff = $this->service->hire($company->id, $form, null);

        $this->tester->wantToTest("check whether staff got all documents from global positions");
        $searchModel = new DocumentFormSearch();
        $searchModel->companyPositionIDs = $staff->getCompanyPositions()->select(['id'])->column();

        verify($searchModel->search([])->count)->equals($documentFormsNumber);
    }

    /**
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function testHireWithoutFreeSlots()
    {
        $this->expectException(\DomainException::class);

        $tariff = $this->tester->getFactory()->create(Tariff::class, [
            'staff_qty' => 3
        ]);
        $company = $this->tester->getFactory()->create(Company::class, [
            'tariff_id' => $tariff->id
        ]);
        $division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $company->id
        ]);
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $divisionService->link('divisions', $division);
        $staffs = $this->tester->getFactory()->seed(3, Staff::class);
        foreach ($staffs as $staff) {
            $staff->link('divisions', $division);
            $staff->link('user', $this->tester->getFactory()->create(User::class, ['company_id' => $company->id]));
        }

        $form = new StaffCreateForm($company->id, [
            'name'                 => $this->tester->getFaker()->name,
            'surname'              => $this->tester->getFaker()->lastName,
            'phone'                => $this->tester->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
            'username'             => $this->tester->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
            'description'          => $this->tester->getFaker()->text(20),
            'gender'               => $this->tester->getFaker()->randomKey(GenderHelper::getGenders()),
            'description_private'  => $this->tester->getFaker()->text(20),
            'color'                => 'color1',
            'company_position_ids' => [],
            'division_service_ids' => [$divisionService->id],
            'division_ids'         => [$division->id],
            'has_calendar'         => false,
            'can_create_order'     => false,
            'create_user'          => true,
            'see_own_orders'       => true,
            'user_permissions'     => [],
        ]);

        $this->service->hire($company->id, $form, null);
    }

    /**
     * @throws \Exception
     */
    public function testRestoreWithoutFreeSlots()
    {
        $this->expectException(\DomainException::class);

        $tariff = $this->tester->getFactory()->create(Tariff::class, [
            'staff_qty' => 3
        ]);
        $company = $this->tester->getFactory()->create(Company::class, [
            'tariff_id' => $tariff->id
        ]);
        $division = $this->tester->getFactory()->create(Division::class, [
            'company_id' => $company->id
        ]);
        $divisionService = $this->tester->getFactory()->create(DivisionService::class);
        $divisionService->link('divisions', $division);
        $staffs = $this->tester->getFactory()->seed(3, Staff::class);
        foreach ($staffs as $staff) {
            $staff->link('divisions', $division);
            $staff->link('user', $this->tester->getFactory()->create(User::class, ['company_id' => $company->id]));
        }
        $deletedStaff = $this->tester->getFactory()->create(Staff::class, [
            'status' => Staff::STATUS_FIRED
        ]);
        $deletedStaff->link('user', $this->tester->getFactory()->create(User::class, [
            'company_id' => $company->id,
            'status'     => User::STATUS_DISABLED
        ]));

        $this->service->restore($deletedStaff->id, $company->id);
    }

    public function testHireAnotherStaffWithDisabledAccount()
    {
        $tariff = $this->tester->getFactory()->create(Tariff::class, ['staff_qty' => 10]);
        $anotherCompany = $this->tester->getFactory()->create(Company::class, ['tariff_id' => $tariff->id]);
        $company = $this->tester->getFactory()->create(Company::class, ['tariff_id' => $tariff->id]);
        $division = $this->tester->getFactory()->create(Division::class, ['company_id' => $company->id]);
        $user = $this->tester->getFactory()->create(User::class, [
            'company_id' => $anotherCompany->id,
            'status'     => User::STATUS_DISABLED
        ]);
        $form = new StaffCreateForm($company->id, [
            'name'                 => $this->tester->getFaker()->firstName,
            'phone'                => $user->username,
            'username'             => $user->username,
            'gender'               => GenderHelper::GENDER_UNDEFINED,
            'color'                => '',
            'create_user'          => true,
            'see_own_orders'       => false,
            'company_position_ids' => [],
            'division_ids'         => [$division->id],
            'division_service_ids' => [],
            'user_divisions'       => [$division->id],
            'user_permissions'     => [],
        ]);

        $staff = $this->service->hire($company->id, $form, null);

        $this->tester->canSeeRecord(Staff::class, [
            'user_id' => $user->id,
            'phone'   => $user->username
        ]);

        $this->tester->canSeeRecord(StaffDivisionMap::class, [
            'staff_id'    => $staff->id,
            'division_id' => $division->id
        ]);

        $this->tester->canSeeRecord(UserDivision::class, [
            'staff_id'    => $staff->id,
            'division_id' => $division->id
        ]);
    }

    public function testHireAnotherStaffWithEnabledAccount()
    {
        $this->expectException(\DomainException::class);

        $tariff = $this->tester->getFactory()->create(Tariff::class, ['staff_qty' => 10]);
        $anotherCompany = $this->tester->getFactory()->create(Company::class, ['tariff_id' => $tariff->id]);
        $company = $this->tester->getFactory()->create(Company::class, ['tariff_id' => $tariff->id]);
        $division = $this->tester->getFactory()->create(Division::class, ['company_id' => $company->id]);
        $user = $this->tester->getFactory()->create(User::class, [
            'company_id' => $anotherCompany->id,
            'status'     => User::STATUS_ENABLED
        ]);
        $form = new StaffCreateForm($company->id, [
            'name'                 => $this->tester->getFaker()->firstName,
            'gender'               => GenderHelper::GENDER_UNDEFINED,
            'phone'                => $user->username,
            'username'             => $user->username,
            'color'                => '',
            'create_user'          => true,
            'see_own_orders'       => false,
            'division_ids'         => [$division->id],
            'company_position_ids' => [],
            'division_service_ids' => [],
            'user_divisions'       => [$division->id],
            'user_permissions'     => [],
        ]);

        $this->service->hire($company->id, $form, null);
    }

    public function testRemoveUserPermissions()
    {
        $tariff = $this->tester->getFactory()->create(Tariff::class, ['staff_qty' => 10]);
        $anotherCompany = $this->tester->getFactory()->create(Company::class, ['tariff_id' => $tariff->id]);
        $company = $this->tester->getFactory()->create(Company::class, ['tariff_id' => $tariff->id]);
        $division = $this->tester->getFactory()->create(Division::class, ['company_id' => $company->id]);
        $user = $this->tester->getFactory()->create(User::class, [
            'company_id' => $anotherCompany->id,
            'status'     => User::STATUS_DISABLED
        ]);
        $form = new StaffCreateForm($company->id, [
            'name'                 => $this->tester->getFaker()->firstName,
            'phone'                => $user->username,
            'username'             => $user->username,
            'gender'               => GenderHelper::GENDER_UNDEFINED,
            'color'                => '',
            'create_user'          => true,
            'see_own_orders'       => false,
            'company_position_ids' => [],
            'division_ids'         => [$division->id],
            'division_service_ids' => [],
            'user_divisions'       => [$division->id],
            'user_permissions'     => [],
        ]);

        $staff = $this->service->hire($company->id, $form, null);

        $this->tester->canSeeRecord(Staff::class, [
            'user_id' => $user->id,
            'phone'   => $user->username
        ]);

        $updateForm = new StaffUpdateForm($staff, [
            'name'                 => $this->tester->getFaker()->firstName,
            'phone'                => $user->username,
            'username'             => $user->username,
            'gender'               => GenderHelper::GENDER_UNDEFINED,
            'color'                => '',
            'create_user'          => false,
            'see_own_orders'       => false,
            'company_position_ids' => [],
            'division_ids'         => [$division->id],
            'division_service_ids' => [],
            'user_divisions'       => [$division->id],
            'user_permissions'     => [],
        ]);

        $this->service->edit($staff->id, $company->id, $updateForm, null);

        $this->tester->cantSeeRecord(Staff::class, [
            'user_id' => $user->id,
            'phone'   => $user->username
        ]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function _before()
    {
        $this->service = \Yii::createObject(StaffModelService::class);
        $this->companyService = \Yii::createObject(CompanyService::class);
    }

    protected function _after()
    {
    }
}