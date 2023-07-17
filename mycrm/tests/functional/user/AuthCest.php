<?php

namespace api\tests\user;

use FunctionalTester;
use core\models\company\Company;
use core\models\ConfirmKey;
use core\models\user\User;
use Yii;

class AuthCest
{
    private $responseFormat = ['token' => 'string'];

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    /**
     * @param FunctionalTester $I
     *
     * @throws \yii\base\Exception
     */
    public function actionLogin(FunctionalTester $I)
    {
        $I->sendGET('user/login');
        $I->seeResponseCodeIs(404);

        $I->sendPOST('user/login');
        $I->seeResponseCodeIs(422);

        $user = $I->getFactory()->create(User::class, [
            'password_hash' => Yii::$app->security->generatePasswordHash('valid_password')
        ]);

        $I->sendPOST('user/login', [
            'username' => $user->username,
            'password' => 'wrong_password',
        ]);
        $I->seeResponseCodeIs(422);

        $I->sendPOST('user/login', [
            'username' => $user->username,
            'password' => 'valid_password',
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }

    public function actionForgotPassword(FunctionalTester $I)
    {
        $I->sendGET('user/forgot-password');
        $I->seeResponseCodeIs(404);

        $I->sendPOST('user/forgot-password');
        $I->seeResponseCodeIs(422);

        $user = $I->getFactory()->create(User::class);

        $I->sendPOST('user/forgot-password', [
            'username' => $user->username
        ]);
        $I->seeResponseCodeIs(200);
    }

    public function actionValidateCode(FunctionalTester $I)
    {
        $I->sendGET('user/validate-code');
        $I->seeResponseCodeIs(404);

        $I->sendPOST('user/validate-code');
        $I->seeResponseCodeIs(422);

        $user = $I->getFactory()->create(User::class);
        $valid_confirm_key = $I->getFactory()->create(ConfirmKey::class, [
            'code' => rand(1000, 9999),
            'username' => $user->username
        ]);
        $wrong_confirm_key = $I->getFactory()->create(ConfirmKey::class, [
            'code' => rand(1000, 9999),
            'username' => $user->username,
            'expired_at' => function () {
                return date('Y-m-d H:i:s', ConfirmKey::EXPIRE_TIME - time());
            }
        ]);

        $I->sendPOST('user/validate-code', [
            'username' => $user->username,
            'code' => $wrong_confirm_key->code,
        ]);
        $I->seeResponseCodeIs(422);

        $I->sendPOST('user/validate-code', [
            'username' => $user->username,
            'code' => $valid_confirm_key->code,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([]);
    }

    public function actionChangePassword(FunctionalTester $I)
    {
        $I->sendGET('user/change-password');
        $I->seeResponseCodeIs(404);

        $I->sendPOST('user/change-password');
        $I->seeResponseCodeIs(422);

        $user = $I->getFactory()->create(User::class);
        $valid_confirm_key = $I->getFactory()->create(ConfirmKey::class, [
            'code' => rand(1000, 9999),
            'username' => $user->username
        ]);
        $wrong_confirm_key = $I->getFactory()->create(ConfirmKey::class, [
            'code' => rand(1000, 9999),
            'username' => $user->username,
            'expired_at' => function () {
                return date('Y-m-d H:i:s', ConfirmKey::EXPIRE_TIME - time());
            }
        ]);

        $I->sendPOST('user/change-password', [
            'username' => $user->username,
            'code' => $wrong_confirm_key->code,
            'password' => 'some_password'
        ]);
        $I->seeResponseCodeIs(422);

        $I->sendPOST('user/change-password', [
            'username' => $user->username,
            'code' => $valid_confirm_key->code,
            'password' => 'some_password'
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType($this->responseFormat);
    }
}
