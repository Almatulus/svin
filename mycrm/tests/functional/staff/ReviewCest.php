<?php

namespace api\tests\staff;

use core\models\Staff;
use core\models\StaffReview;
use FunctionalTester;

class ReviewCest
{
    private $responseFormat = [
        'staff_id' => 'integer',
        'value' => 'integer',
        'comment' => 'string|null',
        'customer_id' => 'integer',
        'created_time' => 'string',
        '_links' => 'array'
    ];

    public function _after(FunctionalTester $I)
    {
    }

    public function index(FunctionalTester $I)
    {
        // Do not need Authorization
        $I->wantToTest('Staff Review index');

        $staff = $I->getFactory()->create(Staff::class, []);

        $myEnabledStaffReview = $I->getFactory()->create(StaffReview::class, [
            'staff_id' => $staff->id,
            'comment' => 'Enabled comment',
            'status' => StaffReview::STATUS_ENABLED,
        ]);
        $myDisabledStaffReview = $I->getFactory()->create(StaffReview::class, [
            'staff_id' => $staff->id,
            'comment' => 'Disabled comment',
            'status' => StaffReview::STATUS_DISABLED,
        ]);
        $otherStaffReview = $I->getFactory()->create(StaffReview::class, [
            'comment' => 'Other comment',
            'status' => StaffReview::STATUS_DISABLED,
        ]);

        $I->sendGET("staff/{$staff->id}/review");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');

        $I->seeResponseContainsJson(['comment' => 'Enabled comment']);
        $I->dontSeeResponseContainsJson(['comment' => 'Disabled comment']);
        $I->dontSeeResponseContainsJson(['comment' => 'Other comment']);
    }

    public function create(FunctionalTester $I)
    {
        // Need Authorization
        $I->wantToTest('Staff Review create');

        $staff = $I->getFactory()->create(Staff::class, []);

        $I->sendPOST("staff/{$staff->id}/review");
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendPOST("staff/{$staff->id}/review", [
            'value' => 99,
            'comment' => 'Some comment',
            'staff_id' => $staff->id
        ]);
        $I->seeResponseCodeIs(403);
//        $I->seeResponseMatchesJsonType($this->responseFormat);

        // P.S. дополнить, когда с user->identity можно будет вытаскивать customer_id
    }

    public function update(FunctionalTester $I)
    {
        // Need Authorization
        $I->wantToTest('Staff Review update');

        $staff = $I->getFactory()->create(Staff::class, []);

        $I->sendPUT("staff/{$staff->id}/review");
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        // some existing comment (to be updated)
        $staffReview = $I->getFactory()->create(StaffReview::class, [
            'staff_id' => $staff->id,
            'comment' => 'Some comment',
        ]);

        $I->sendPUT("staff/{$staff->id}/review", [

        ]);
        $I->seeResponseCodeIs(403);
        // P.S. дополнить, когда с user->identity можно будет вытаскивать customer_id
    }
}
