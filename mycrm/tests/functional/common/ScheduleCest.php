<?php

namespace common;


use core\models\company\Company;
use core\models\division\Division;
use core\models\Staff;
use core\models\StaffSchedule;
use FunctionalTester;

class ScheduleCest
{
    private $_responseFormat = [
        'id'          => 'integer',
        'division_id' => 'integer',
        'staff_id'    => 'integer',
        'start_at'    => 'string',
        'end_at'      => 'string',
        'break_start' => 'string|null',
        'break_end'   => 'string|null'
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
        $company = $I->getFactory()->create(Company::class, [
            'enable_integration' => true
        ]);
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id
        ]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $schedule = $I->getFactory()->create(StaffSchedule::class,
            ['staff_id' => $staff->id, 'division_id' => $division->id]);

        $I->sendGET("public/schedule", [
            'staff_id'    => $schedule->staff_id,
            'division_id' => $schedule->division_id,
            'start_at'    => (new \DateTime($schedule->start_at))->format("Y-m-d"),
            'end_at'    => (new \DateTime($schedule->start_at))->modify('+2 days')->format("Y-m-d")
        ]);
        $I->seeResponseMatchesJsonType($this->_responseFormat, "$.[*]");
    }

    public function indexError(FunctionalTester $I)
    {
        $company = $I->getFactory()->create(Company::class, [
            'enable_integration' => false
        ]);
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id
        ]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $schedule = $I->getFactory()->create(StaffSchedule::class,
            ['staff_id' => $staff->id, 'division_id' => $division->id]);

        $I->sendGET("public/schedule", [
            'staff_id'    => $schedule->staff_id,
            'division_id' => $schedule->division_id,
            'start_at'    => (new \DateTime($schedule->start_at))->format("Y-m-d"),
            'end_at'    => (new \DateTime($schedule->start_at))->modify('+2 days')->format("Y-m-d")
        ]);
        $I->seeResponseMatchesJsonType([], "$.[*]");
    }


    public function indexWithLimitedEndDate(FunctionalTester $I)
    {
        $company = $I->getFactory()->create(Company::class, [
            'enable_integration' => false
        ]);
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id
        ]);
        $staff = $I->getFactory()->create(Staff::class);
        $staff->link('divisions', $division);
        $schedule = $I->getFactory()->create(StaffSchedule::class,
            ['staff_id' => $staff->id, 'division_id' => $division->id]);

        $I->sendGET("public/schedule", [
            'staff_id'    => $schedule->staff_id,
            'division_id' => $schedule->division_id,
            'start_at'    => (new \DateTime($schedule->start_at))->format("Y-m-d"),
            'end_at'    => (new \DateTime($schedule->start_at))->modify('+2 month')->format("Y-m-d")
        ]);
        $I->seeResponseCodeIs(422);
    }
}
