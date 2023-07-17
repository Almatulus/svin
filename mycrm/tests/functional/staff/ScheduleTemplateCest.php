<?php

namespace staff;


use core\helpers\ScheduleTemplateHelper;
use core\models\division\Division;
use core\models\ScheduleTemplate;
use core\models\ScheduleTemplateInterval;
use core\models\Staff;
use FunctionalTester;

class ScheduleTemplateCest
{
    private $responseFormat = [
        'id'            => 'integer',
        'interval_type' => 'integer',
        'division_id'   => 'integer',
        'staff_id'      => 'integer',
        'type'          => 'integer',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'created_at'    => 'string',
        'updated_at'    => 'string',
        'intervals'     => 'array|null'
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
        $I->sendGET('staff/1/schedule/template');
        $I->seeResponseCodeIs(401);

        $user = $I->login();
        \Yii::$app->set('user', $user);

        $notOwnStaff = $I->getFactory()->create(Staff::class);
        $notOwnDivision = $I->getFactory()->create(Division::class);
        $notOwnStaff->link('divisions', $notOwnDivision);

        $staff = $I->getFactory()->create(Staff::class);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff->link('divisions', $division);

        /** @var ScheduleTemplate $scheduleTemplate */
        $scheduleTemplate = $I->getFactory()->create(ScheduleTemplate::class, [
            'staff_id'    => $staff->id,
            'division_id' => $division->id
        ]);
        $scheduleTemplateInterval = $I->getFactory()->create(ScheduleTemplateInterval::class, [
            'schedule_template_id' => $scheduleTemplate->id
        ]);

        $I->sendGET("staff/{$notOwnStaff->id}/schedule/template?expand=intervals");
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([]);

        $I->sendGET("staff/{$staff->id}/schedule/template?expand=intervals");
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat, '$.[*]');
    }

    public function generate(FunctionalTester $I)
    {
        $I->sendPOST('staff/1/schedule/template');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $notOwnStaff = $I->getFactory()->create(Staff::class);
        $notOwnDivision = $I->getFactory()->create(Division::class);
        $notOwnStaff->link('divisions', $notOwnDivision);

        $staff = $I->getFactory()->create(Staff::class);
        $division = $I->getFactory()->create(Division::class, ['company_id' => $user->company_id]);
        $staff->link('divisions', $division);

        $I->sendPOST("staff/{$notOwnStaff->id}/schedule/template?expand=intervals");
        $I->seeResponseCodeIs(403);

        $I->sendPOST("staff/{$staff->id}/schedule/template?expand=intervals", [
            'division_id'   => $notOwnDivision->id,
            'start'         => date("Y-m-d"),
            'type'          => ScheduleTemplateHelper::TYPE_DAYS_OF_WEEK,
            'interval_type' => ScheduleTemplateHelper::PERIOD_TWO_WEEKS,
            'intervals'     => [
                1 => [
                    'start'       => "09:00",
                    'end'         => "22:00",
                    'break_start' => "14:00",
                    'break_end'   => "15:00",
                ],
            ]
        ]);
        $I->seeResponseCodeIs(422);

        $I->sendPOST("staff/{$staff->id}/schedule/template?expand=intervals", [
            'division_id'   => $division->id,
            'start'         => date("Y-m-d"),
            'type'          => ScheduleTemplateHelper::TYPE_DAYS_OF_WEEK,
            'interval_type' => ScheduleTemplateHelper::PERIOD_TWO_WEEKS,
            'intervals'     => [
                1 => [
                    'is_enabled'  => true,
                    'start'       => "09:00",
                    'end'         => "22:00",
                    'break_start' => "14:00",
                    'break_end'   => "15:00",
                ],
                2 => [
                    'is_enabled'  => true,
                    'start'       => "10:00",
                    'end'         => "21:00",
                    'break_start' => "14:00",
                    'break_end'   => "15:00",
                ],
                4 => [
                    'is_enabled'  => true,
                    'start'       => "10:00",
                    'end'         => "21:00",
                    'break_start' => "14:00",
                    'break_end'   => "15:00",
                ],
            ]
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

}
