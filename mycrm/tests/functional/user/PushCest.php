<?php

namespace api\tests\user;

use FunctionalTester;
use core\models\user\User;

class PushCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function key(FunctionalTester $I)
    {
        $I->sendGET('user/push/key');
        $I->seeResponseCodeIs(401);

        $user = $I->login();

        $I->sendPOST("user/push/key");
        $I->seeResponseCodeIs(422);

        $key = $I->getFaker()->text(14);
        $I->sendPOST("user/push/key", ['key' => $key]);
        $I->seeResponseCodeIs(200);

        $I->canSeeRecord(User::class, [
            'id'         => $user->id,
            'device_key' => $key
        ]);
    }

}
