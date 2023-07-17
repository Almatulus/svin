<?php

namespace api\tests\order;

use core\models\division\Division;
use core\models\Staff;
use core\models\StaffDivisionMap;
use core\models\StaffSchedule;
use FunctionalTester;

class TimetableCest
{
    private $responseFormat
        = [
            'staff' => 'array',
            'selected_staff'  => 'array',
            'duration'  => 'array'
        ];

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Timetable index');
        $I->sendGET('order/timetable/index');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $staff = $I->getFactory()->create(Staff::class, [
            'user_id' => $user->id,
            'has_calendar' => true
        ]);

        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $user->company_id,
        ]);

        $staffDivisionMap = $I->getFactory()->create(StaffDivisionMap::class, [
            'staff_id' => $staff->id,
            'division_id' => $division->id
        ]);

        $staffSchedule = $I->getFactory()->create(StaffSchedule::class, [
            'staff_id' => $staff->id,
            'division_id' => $division->id,
            'start_at' => date('Y-m-d H:i:s', strtotime('today')),
            'end_at' => date('Y-m-d H:i:s', strtotime('tomorrow'))
        ]);

        $I->sendGET('order/timetable/index');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);

        //  Ensure that response contains not empty staff array
        $I->seeResponseContains("\"staff\": [\n");
        //  Ensure that response contains not empty selected_staff array
        $I->seeResponseContains("\"selected_staff\": [\n");
        //  Ensure that company has schedule duration
        $I->seeResponseContains("min");
    }

    public function indexSeeOwnOrders(FunctionalTester $I)
    {
        $user = $I->login();

        $staff = $I->getFactory()->create(Staff::class, [
            'user_id' => $user->id,
            'see_own_orders' => true,
            'has_calendar' => true
        ]);

        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $user->company_id,
        ]);

        $staffDivisionMap = $I->getFactory()->create(StaffDivisionMap::class, [
            'staff_id' => $staff->id,
            'division_id' => $division->id
        ]);

        $I->getFactory()->create(StaffSchedule::class, [
            'staff_id' => $staff->id,
            'division_id' => $division->id,
            'start_at' => date('Y-m-d H:i:s', strtotime('today')),
            'end_at' => date('Y-m-d H:i:s', strtotime('tomorrow'))
        ]);

        // Another staff
        $anotherStaff = $I->getFactory()->create(Staff::class, [
            'has_calendar' => true
        ]);
        $staffDivisionMap = $I->getFactory()->create(StaffDivisionMap::class, [
            'staff_id' => $anotherStaff->id,
            'division_id' => $division->id
        ]);
        $I->getFactory()->create(StaffSchedule::class, [
            'staff_id' => $anotherStaff->id,
            'division_id' => $division->id,
            'start_at' => date('Y-m-d H:i:s', strtotime('today')),
            'end_at' => date('Y-m-d H:i:s', strtotime('tomorrow'))
        ]);

        $I->sendGET('order/timetable/index');
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);

        $staff_attributes = json_encode($staff->fields());
        //  Ensure that response contains not empty staff array
        $I->seeResponseContains("\"staff\": [\n");
        //  Ensure that response contains not empty selected_staff array
        $I->seeResponseContains("{$staff->id}");
        $I->cantSeeResponseContains("{$anotherStaff->id}");
        //  Ensure that company has schedule duration
        $I->seeResponseContains("min");
    }
}
