<?php

namespace api\tests\user;

use core\helpers\GenderHelper;
use core\helpers\order\OrderConstants;
use core\helpers\StaffHelper;
use core\models\company\CompanyPosition;
use core\models\company\Tariff;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\order\Order;
use core\models\Staff;
use core\models\StaffDivisionMap;
use core\models\user\User;
use core\models\user\UserDivision;
use FunctionalTester;

class StaffCest
{
    private $responseFormat = [
        'id'                   => 'integer',
        'name'                 => 'string',
        'surname'              => 'string|null',
        'phone'                => 'string|null',
        'description'          => 'string|null',
        'description_private'  => 'string|null',
        'gender'               => 'integer',
        'gender_name'          => 'string',
        'has_calendar'         => 'integer',
        'color'                => 'string',
        'can_create_order'     => 'boolean',
        'rating'               => 'string',
        'image'                => 'string',
        'has_user_permissions' => 'boolean',
        'services'             => 'array|null',
        'divisions'            => 'array|null',
        'position'             => 'array|null',
        'reviews'              => 'array|null',
        'user_divisions'       => 'array|null',
        'user_permissions'     => 'array|null'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('user/staff');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $firstName = $I->getFaker()->firstName;
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staffs = $I->getFactory()->seed(2, Staff::class, ['name' => $firstName]);

        foreach ($staffs as $staff) {
            $I->getFactory()->create(StaffDivisionMap::class, [
                'division_id' => $division->id,
                'staff_id'    => $staff->id
            ]);
        }

        $I->sendGET("user/staff?expand=divisions,position,reviews,user_divisions,user_permissions,services,rating&term={$firstName}");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function view(FunctionalTester $I)
    {
        $I->sendGET('user/staff/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $I->getFactory()->create(UserDivision::class, [
            'staff_id'    => $staff->id,
            'division_id' => $division->id
        ]);

        $I->sendGET("user/staff/{$staff->id}?expand=divisions,position,reviews,user_divisions,user_permissions,services,rating");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Staff creation');

        $I->amGoingTo('Create staff without authorization');
        $I->sendPOST('user/staff/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->amGoingTo('Create staff with invalid data');
        $I->sendPOST('user/staff', [
            'name' => ''
        ]);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Create staff who exceeds limit of employees for company');

        $tariff = $I->getFactory()->create(Tariff::class, ['staff_qty' => 1]);
        $staff = $this->getOwnStaff($user, $I);

        $user->company->updateAttributes(['tariff_id' => $tariff->id]);

        $companyPosition = $I->getFactory()->create(CompanyPosition::class, ['company_id' => $user->company_id]);
        $data = [
            'name'                 => $I->getFaker()->firstName,
            'surname'              => $I->getFaker()->lastName,
            'company_position_ids' => [$companyPosition->id],
            'phone'                => $I->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
            'username'             => $I->getFaker()->regexify("\+7 \d{3} \d{3} \d{2} \d{2}"),
            'description'          => $I->getFaker()->text(10),
            'gender'               => $I->getFaker()->randomKey(GenderHelper::getGenders()),
            'description_private'  => $I->getFaker()->text(10),
            'color'                => $I->getFaker()->randomKey(StaffHelper::getCssClasses()),
            'division_ids'         => $staff->getDivisions()->select('id')->column(),
            'has_calendar'         => true,
            'create_user'          => true,
            'see_own_orders'       => true,
            'user_permissions'     => ['timetableView'],
        ];
        $I->sendPOST('user/staff', $data);
        $I->seeResponseCodeIs(500);

        // increase tariff limit to create staff
        $tariff->updateAttributes(['staff_qty' => 2]);
        $I->amGoingTo('Create staff with valid data');
        $I->sendPOST('user/staff?expand=divisions,position,reviews,user_divisions,user_permissions,services,rating', $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Staff update');

        $I->amGoingTo('Update staff without authorization');
        $I->sendPUT('user/staff/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $notOwnedStaff = $I->getFactory()->create(Staff::class);
        $I->amGoingTo('Update staff without access to resource');
        $I->sendPUT("user/staff/{$notOwnedStaff->id}", ['name' => '']);
        $I->seeResponseCodeIs(403);

        $staff = $this->getOwnStaff($user, $I);
        $I->amGoingTo('Update staff with invalid data');
        $I->sendPUT("user/staff/{$staff->id}", ['name' => '']);
        $I->seeResponseCodeIs(422);

        $companyPosition = $I->getFactory()->create(CompanyPosition::class, ['company_id' => $user->company_id]);
        $data = [
            'name'                => $I->getFaker()->firstName,
            'surname'             => $I->getFaker()->lastName,
            'company_position_ids' => [$companyPosition->id],
            'description'         => $I->getFaker()->text(10),
            'gender'              => $I->getFaker()->randomKey(GenderHelper::getGenders()),
            'description_private' => $I->getFaker()->text(10),
            'color'               => $I->getFaker()->randomKey(StaffHelper::getCssClasses()),
            'division_ids'        => $staff->getDivisions()->select('id')->column(),
            'has_calendar'        => true,
            'create_user'         => false,
        ];
        $I->amGoingTo('Update staff with valid data');
        $I->sendPUT("user/staff/{$staff->id}?expand=divisions,position,reviews,user_divisions,user_permissions,services,rating",
            $data);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function fire(FunctionalTester $I)
    {
        $I->wantToTest('Staff fire');

        $I->amGoingTo('Fire staff without authorization');
        $I->sendDELETE('user/staff/1');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $notOwnedStaff = $I->getFactory()->create(Staff::class);
        $I->amGoingTo('Fire staff without access to resource');
        $I->sendDELETE("user/staff/{$notOwnedStaff->id}");
        $I->seeResponseCodeIs(403);

        $anotherUser = $I->getFactory()->create(User::class, ['company_id' => $user->company_id]);
        $staff = $this->getOwnStaff($anotherUser, $I);
        $I->getFactory()->create(Order::class, [
            "datetime" => (new \DateTime())->modify("+1 day")->format("Y-m-d H:i:s"),
            'status'   => OrderConstants::STATUS_ENABLED,
            "staff_id" => $staff->id
        ]);
        $I->amGoingTo('Fire staff with forthcoming orders');
        $I->sendDELETE("user/staff/{$staff->id}");
        $I->seeResponseCodeIs(500);

        $staff = $this->getOwnStaff($user, $I);
        $I->amGoingTo('Fire staff');
        $I->sendDELETE("user/staff/{$staff->id}");
        $I->seeResponseCodeIs(204);
    }

    public function addServices(FunctionalTester $I)
    {
        $I->wantToTest('Adding services to staff');

        $I->amGoingTo('Add services to staff without authorization');
        $I->sendPOST('user/staff/1/service/add');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $staff = $this->getOwnStaff($user, $I);

        $I->amGoingTo('Add services to staff with invalid data');
        $I->sendPOST("user/staff/{$staff->id}/service/add", ["services" => [0]]);
        $I->seeResponseCodeIs(422);

        $services = $I->getFactory()->seed(2, DivisionService::class);
        foreach ($services as $service) {
            $service->link('divisions', $staff->divisions[0]);
        }
        $I->amGoingTo('Add services to staff with valid data');
        $I->sendPOST("user/staff/{$staff->id}/service/add", [
            "services" => [$services[0]->id, $services[1]->id]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([$services[0]->id, $services[1]->id]);
    }

    public function deleteServices(FunctionalTester $I)
    {
        $I->wantToTest('Deleting services of staff');

        $I->amGoingTo('Delete services of staff without authorization');
        $I->sendPOST('user/staff/1/service/delete');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $staff = $this->getOwnStaff($user, $I);
        $services = $I->getFactory()->seed(2, DivisionService::class);
        foreach ($services as $service) {
            $service->link('divisions', $staff->divisions[0]);
        }
        foreach ($services as $service) {
            $staff->link('divisionServices', $service);
        }

        $I->amGoingTo('Delete services of staff with invalid data');
        $I->sendPOST("user/staff/{$staff->id}/service/delete", ["services" => [0]]);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Delete services of staff with valid data');
        $I->sendPOST("user/staff/{$staff->id}/service/delete", [
            "services" => [$services[0]->id, $services[1]->id]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([]);
    }

    /**
     * @param $user
     * @param FunctionalTester $I
     * @return Staff
     */
    private function getOwnStaff($user, FunctionalTester $I)
    {
        /** @var Division $division */
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        /** @var Staff $staff */
        $staff = $I->getFactory()->create(Staff::class, ['user_id' => $user->id]);
        $staff->link('divisions', $division);
        $I->getFactory()->create(UserDivision::class, [
            'staff_id'    => $staff->id,
            'division_id' => $division->id
        ]);

        return $staff;
    }
}
