<?php

namespace tests\codeception\unit\models;

use Codeception\Specify;
use core\forms\LoginForm;
use core\models\company\Company;
use core\models\user\User;
use Yii;

class LoginFormTest extends \Codeception\TestCase\Test
{
    use Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var User
     */
    private $user;

    protected function _before()
    {
        if (!$this->user) {
            $this->user = $this->tester->getFactory()->create(User::class);
        }
    }

    /**
     * @group future
     */
    public function testLoginNoUser()
    {
        $model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        $this->specify('user should not be able to login, when there is no identity', function () use ($model) {
            expect('model should not login user', $model->login())->false();
            expect('user should not be logged in', Yii::$app->user->isGuest)->true();
        });
    }

    /**
     * @group future
     */
    public function testLoginWrongPassword()
    {
        $model = new LoginForm([
            'username' => $this->user->username,
            'password' => 'wrong_password',
        ]);

        $this->specify('user should not be able to login with wrong password', function () use ($model) {
            expect('model should not login user', $model->login())->false();
            expect('error message should be set', $model->errors)->hasKey('password');
            expect('user should not be logged in', Yii::$app->user->isGuest)->true();
        });
    }

    /**
     * @group future
     */
    public function testLoginCorrect()
    {
        $model = new LoginForm([
            'username' => $this->user->username,
            'password' => Yii::$app->params['password'],
        ]);

        $this->specify('user should be able to login with correct credentials', function () use ($model) {
            expect('model should login user', $model->login())->true();
            expect('error message should not be set', $model->errors)->hasntKey('password');
            expect('user should be logged in', Yii::$app->user->isGuest)->false();
        });
    }

    /**
     * @group future
     */
    public function testLoginDisabledCompany()
    {
        $company = $this->tester->getFactory()->create(Company::class, ['status' => Company::STATUS_DISABLED]);
        $user = $this->tester->getFactory()->create(User::class, [
            'company_id' => $company->id
        ]);

        $model = new LoginForm([
            'username' => $user->username,
            'password' => Yii::$app->params['password'],
        ]);

        $this->specify('disabled company user shouldn\'t be able to login', function () use ($model) {
            expect('model should not login user', $model->login())->false();
            expect('error message should be set', $model->errors)->hasKey('password');
            expect('user should not be logged in', Yii::$app->user->isGuest)->true();
        });
    }

}
