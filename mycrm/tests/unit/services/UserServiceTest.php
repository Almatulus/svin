<?php

namespace services;

use Codeception\Specify;
use core\models\company\Company;
use core\models\rbac\AuthAssignment;
use core\models\user\User;
use core\services\user\UserService;

class UserServiceTest extends \Codeception\Test\Unit
{
    use Specify;

    /**
     * @var UserService
     */
    private $_service;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var Company
     */
    private $company;
    /**
     * @var string
     */
    private $role;
    /**
     * @var User
     */
    private $user;

    protected function _before()
    {
        $this->_service = \Yii::createObject(UserService::class);

        if (!$this->company) {
            $this->company = $this->tester->getFactory()->create(Company::class);
        }

        if (!$this->user) {
            $this->user = $this->tester->getFactory()->create(User::class);
        }

        $this->role = 'company';
    }

    protected function _after()
    {
    }

    public function testCreateWithEmptyUsername()
    {
        $this->expectException(\DomainException::class);
        $this->_service->create(
            $this->company->id,
            $this->tester->getFaker()->password(),
            $this->role,
            Company::STATUS_ENABLED,
            null,
            null
        );
    }

    public function testCreateUserWithInvalidUsername()
    {
        $this->expectException(\DomainException::class);
        $this->_service->create(
            $this->company->id,
            $this->tester->getFaker()->password(),
            $this->role,
            Company::STATUS_ENABLED,
            $this->tester->getFaker()->regexify('/^\+[0-9] [0-9]{3} [0-9]{3} [0-9]{2}'),
            null
        );
    }

    public function testCreateSuccessfully()
    {
        $user = $this->_service->create(
            $this->company->id,
            $this->tester->getFaker()->password(),
            $this->role,
            Company::STATUS_ENABLED,
            $this->tester->getFaker()->regexify('/^\+[0-9] [0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}'),
            [
                'orderOwner',
            ]
        );

        expect("User Model", $user)->isInstanceOf(User::class);
        expect("User Model id is not empty", $user->id)->notNull();
        $this->tester->canSeeRecord(AuthAssignment::className(), [
            'item_name' => 'company',
            'user_id'   => $user->id
        ]);
        $this->tester->canSeeRecord(AuthAssignment::className(), [
            'item_name' => 'orderOwner',
            'user_id'   => $user->id
        ]);
    }

    public function testEdit()
    {
        $user = $this->tester->getFactory()->create(User::class);
        $user->setAccesses(['orderOwner']);
        $newUsername = $this->tester->getFaker()->regexify('/^\+[0-9] [0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}');
        $user = $this->_service->edit(
            $user->id,
            $this->company->id,
            null,
            'administrator',
            Company::STATUS_ENABLED,
            $newUsername,
            ['companyCustomerOwner']
        );

        expect("User company_id attribute was edited", $user->company_id)->equals(
            $this->company->id
        );
        expect("User username attribute was edited", $user->username)->equals(
            $newUsername
        );

        $this->tester->canSeeRecord(AuthAssignment::className(), [
            'item_name' => 'administrator',
            'user_id'   => $user->id
        ]);
        $this->tester->canSeeRecord(AuthAssignment::className(), [
            'item_name' => 'companyCustomerOwner',
            'user_id'   => $user->id
        ]);
        $this->tester->cantSeeRecord(AuthAssignment::className(), [
            'item_name' => 'orderOwner',
            'user_id'   => $user->id
        ]);
    }

    public function testEditPasswordToEmpty()
    {
        $this->expectException(\DomainException::class);
        $this->_service->editPassword(
            $this->user->id,
            null
        );
    }

    public function testEditPasswordToShort()
    {
        $this->expectException(\DomainException::class);
        $this->_service->editPassword(
            $this->user->id,
            $this->tester->getFaker()->password(1, 5)
        );
    }

    public function testEditPasswordSuccessfully()
    {
        $password = $this->tester->getFaker()->password(6, 20);
        $user = $this->_service->editPassword(
            $this->user->id,
            $password
        );

        expect("Successful password validation", $user->validatePassword($password));
    }


}