<?php

namespace helpers;

use app\tests\fixtures\StaffFixture;
use app\tests\fixtures\UserFixture;
use core\helpers\ICalendar;

class ICalendarTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->markTestSkipped();
        $this->tester->haveFixtures([
            'staff' => [
                'class' => StaffFixture::className(),
            ],
            'user' => [
                'class' => UserFixture::className(),
            ]
        ]);
        $this->login();
    }

    // tests
    public function testGeneration()
    {
        $calendar = ICalendar::generate();
        expect("generation of text representation of calendar in iCalFormat", $calendar)
            ->startsWith("BEGIN:VCALENDAR");
        expect("generation of text representation of calendar in iCalFormat", $calendar)
            ->endsWith("END:VCALENDAR");
    }

    protected function login()
    {
        $user = $this->tester->grabFixture('user', "user_2");
        \Yii::$app->user->login($user, 3600 * 24 * 30);
    }
}