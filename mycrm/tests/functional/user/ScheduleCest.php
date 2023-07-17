<?php

namespace api\tests\user;

use core\models\division\Division;
use core\models\order\Order;
use core\models\Staff;
use core\models\StaffDivisionMap;
use core\models\StaffSchedule;
use FunctionalTester;

class ScheduleCest
{
    private $responseFormat = [
        'id'          => 'integer',
        'start_at'    => 'string',
        'end_at'      => 'string',
        'break_start' => 'string|null',
        'break_end'   => 'string|null',
        "staff_id"    => 'integer',
        "division_id" => 'integer'
    ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->sendGET('user/schedule');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staffs = $I->getFactory()->seed(2, Staff::class);

        foreach ($staffs as $staff) {
            $I->getFactory()->create(StaffDivisionMap::class, [
                'division_id' => $division->id,
                'staff_id'    => $staff->id
            ]);
        }

        foreach ($staffs as $staff) {
            $I->getFactory()->create(Order::class, [
                'division_id' => $division->id,
                'staff_id'    => $staff->id,
            ]);
        }

        $I->sendGET("user/schedule", [
            'date'        => date('Y-m-d'),
            'division_id' => $division->id,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'id'          => 'integer',
            'start'       => 'string|null',
            'end'         => 'string|null',
            'break_start' => 'string|null',
            'break_end'   => 'string|null',
            'orders'      => 'array',
            'staff'       => 'array',
        ], '$.[*]');
    }

    public function week(FunctionalTester $I)
    {
        $I->sendGET('user/schedule/week');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staffs = $I->getFactory()->seed(2, Staff::class);

        foreach ($staffs as $staff) {
            $I->getFactory()->create(StaffDivisionMap::class, [
                'division_id' => $division->id,
                'staff_id'    => $staff->id
            ]);
        }

        foreach ($staffs as $staff) {
            $I->getFactory()->create(Order::class, [
                'division_id' => $division->id,
                'staff_id'    => $staff->id,
            ]);
        }

        $I->sendGET("user/schedule/week", [
            'date'        => date('Y-m-d'),
            'division_id' => $division->id,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'id'        => 'integer',
            'staff'     => 'array',
            'schedules' => [
                date("Y-m-d") => [
                    'start'       => 'string|null',
                    'end'         => 'string|null',
                    'break_start' => 'string|null',
                    'break_end'   => 'string|null',
                    'orders'      => 'array'
                ]
            ]
        ], '$.[*]');
    }

    public function create(FunctionalTester $I)
    {
        $I->wantToTest('Schedule creation');

        $I->sendPOST('user/schedule');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $anotherDivision = $I->getFactory()->create(Division::class);
        $anotherStaff = $I->getFactory()->create(Staff::class);
        $anotherStaff->link('divisions', $anotherDivision);

        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);

        $I->amGoingTo('Create schedule with empty data');
        $I->sendPOST('user/schedule');
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Create schedule for division of another company');
        $I->sendPOST('user/schedule', [
            'staff_id'    => $staff->id,
            'division_id' => $anotherDivision->id,
            'date'        => date("Y-m-d"),
            'start'       => "10:00",
            'end'         => "20:00"
        ]);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Create schedule for staff of another company');
        $I->sendPOST('user/schedule', [
            'staff_id'    => $anotherStaff->id,
            'division_id' => $division->id,
            'date'        => date("Y-m-d"),
            'start'       => "10:00",
            'end'         => "20:00"
        ]);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Create schedule with valid data');
        $I->sendPOST('user/schedule', [
            'staff_id'    => $staff->id,
            'division_id' => $division->id,
            'date'        => date("Y-m-d"),
            'start'       => "10:00",
            'end'         => "20:00",
            'break_start' => "15:00",
            'break_end'   => "16:00",
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function update(FunctionalTester $I)
    {
        $I->wantToTest('Schedule update');

        $I->sendPUT('user/schedule');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $anotherDivision = $I->getFactory()->create(Division::class);
        $anotherStaff = $I->getFactory()->create(Staff::class);
        $anotherStaff->link('divisions', $anotherDivision);
        $anotherSchedule = $I->getFactory()->create(StaffSchedule::class, [
            'division_id' => $anotherDivision->id,
            'staff_id'    => $anotherStaff->id
        ]);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $schedule = $I->getFactory()->create(StaffSchedule::class, [
            'division_id' => $division->id,
            'staff_id'    => $staff->id
        ]);

//        $I->amGoingTo('Update non-existing schedule');
//        $I->sendPUT('user/schedule', [
//            'staff_id'    => $staff->id,
//            'division_id' => $division->id,
//            'date'        => (new \DateTime())->modify("+5 days")->format("Y-m-d"),
//            'start'    => "10:00",
//            'end'      => "20:00"
//        ]);
//        $I->seeResponseCodeIs(500);

        $I->amGoingTo('Update schedule with empty data');
        $I->sendPUT('user/schedule');
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Update schedule of division of another company');
        $I->sendPUT('user/schedule', [
            'staff_id'    => $staff->id,
            'division_id' => $anotherDivision->id,
            'date'        => date("Y-m-d"),
            'start'       => "10:00",
            'end'         => "20:00"
        ]);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Update schedule of staff of another company');
        $I->sendPUT('user/schedule', [
            'staff_id'    => $anotherStaff->id,
            'division_id' => $division->id,
            'date'        => date("Y-m-d"),
            'start'       => "10:00",
            'end'         => "20:00"
        ]);
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Update schedule with valid data');
        $I->sendPUT('user/schedule', [
            'staff_id'    => $staff->id,
            'division_id' => $division->id,
            'date'        => date("Y-m-d"),
            'start'       => "10:00",
            'end'         => "20:00",
            'break_start' => "15:00",
            'break_end'   => "16:00",
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function delete(FunctionalTester $I)
    {
        $I->wantToTest('Schedule delete');

        $I->sendDELETE('user/schedule');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $anotherDivision = $I->getFactory()->create(Division::class);
        $anotherStaff = $I->getFactory()->create(Staff::class);
        $anotherStaff->link('divisions', $anotherDivision);
        $anotherSchedule = $I->getFactory()->create(StaffSchedule::class, [
            'division_id' => $anotherDivision->id,
            'staff_id'    => $anotherStaff->id
        ]);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $schedule = $I->getFactory()->create(StaffSchedule::class, [
            'division_id' => $division->id,
            'staff_id'    => $staff->id
        ]);

//        $I->amGoingTo('Delete non-existing schedule');
//        $I->$this->sendDELETE('user/schedule', [
//            'staff_id'    => $staff->id,
//            'division_id' => $division->id,
//            'date'        => (new \DateTime())->modify("+5 days")->format("Y-m-d"),
//            'start'    => "10:00",
//            'end'      => "20:00"
//        ]);
//        $I->seeResponseCodeIs(500);

        $I->amGoingTo('Delete schedule with empty data');
        $I->sendDELETE('user/schedule');
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Delete schedule of division of another company');
        $I->sendDELETE('user/schedule?' . http_build_query([
                'staff_id'    => $staff->id,
                'division_id' => $anotherDivision->id,
                'date'        => date("Y-m-d")
            ]));
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Delete schedule of staff of another company');
        $I->sendDELETE('user/schedule?' . http_build_query([
                'staff_id'    => $anotherStaff->id,
                'division_id' => $division->id,
                'date'        => date("Y-m-d")
            ]));
        $I->seeResponseCodeIs(422);

        $I->amGoingTo('Delete schedule with valid data');
        $I->sendDELETE('user/schedule?' . http_build_query([
                'staff_id'    => $staff->id,
                'division_id' => $division->id,
                'date'        => date("Y-m-d")
            ]));
        $I->seeResponseCodeIs(204);
    }
}
