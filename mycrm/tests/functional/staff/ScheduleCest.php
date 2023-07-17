<?php

namespace api\tests\schedule;

use FunctionalTester;
use core\models\company\Company;
use core\models\division\Division;
use core\models\Staff;
use core\models\StaffSchedule;
use core\models\user\User;

class ScheduleCest
{
    private $responseFormat
        = [
            'id'          => 'integer',
            'staff_id'    => 'integer',
            'start_at'    => 'string',
            'end_at'      => 'string',
            'division_id' => 'integer',
        ];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        $I->wantToTest('Staff schedule index');

        $company  = $I->getFactory()->create(Company::class);
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id,
        ]);
        $staff    = $I->getFactory()->create(Staff::class, [
            'status' => Staff::STATUS_ENABLED,
        ]);
        $I->getFactory()->create(StaffSchedule::class, [
            "staff_id"    => $staff->id,
            "start_at"    => date('Y-m-d 09:00:00'),
            "end_at"      => date('Y-m-d 20:00:00'),
            "division_id" => $division->id,
        ]);

        $I->sendGET("division/{$division->id}/staff/{$staff->id}/schedule");
        $I->seeResponseCodeIs(401);

        $I->login();
        $I->sendGET("division/{$division->id}/staff/{$staff->id}/schedule", [
            'start_time'  => date('Y-m-d 00:00:00'),
            'finish_time' => date('Y-m-d 23:59:59'),
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        $I->sendGET("division/{$division->id}/staff/{$staff->id}/schedule", [
            'start_time'  => date('Y-m-d 00:00:00'),
            'finish_time' => date('Y-m-d 00:00:00'),
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals(json_encode([]));
    }

    public function authTime(FunctionalTester $I)
    {
        $I->wantToTest('Staff schedule auth time');

        $company  = $I->getFactory()->create(Company::class,[
            'limit_auth_time_by_schedule' => true
        ]);
        $division = $I->getFactory()->create(Division::class, [
            'company_id' => $company->id,
        ]);

        $user = $I->getFactory()->create(User::class, [
            'status' => Staff::STATUS_ENABLED,
            'company_id' => $company->id
        ]);
        $I->amBearerAuthenticated($user->access_token);

        $staff    = $I->getFactory()->create(Staff::class, [
            'status' => Staff::STATUS_ENABLED,
            'user_id' => $user->id
        ]);

        $I->getFactory()->create(StaffSchedule::class, [
            "staff_id"    => $staff->id,
            "start_at"    => date('Y-m-d H:i:s', time() - 10),
            "end_at"      => date('Y-m-d H:i:s', time() + 2),
            "division_id" => $division->id,
        ]);

        $I->sendGET("division/{$division->id}/staff/{$staff->id}/schedule", [
            'start_time'  => date('Y-m-d 00:00:00'),
            'finish_time' => date('Y-m-d 23:59:59'),
        ]);
        $I->seeResponseCodeIs(200);

        // Wait until schedule ends
        sleep(3);

        $I->sendGET("division/{$division->id}/staff/{$staff->id}/schedule", [
            'start_time'  => date('Y-m-d 00:00:00'),
            'finish_time' => date('Y-m-d 23:59:59'),
        ]);
        $I->seeResponseCodeIs(401);
    }
}
