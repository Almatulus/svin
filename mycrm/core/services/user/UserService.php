<?php

namespace core\services\user;

use core\helpers\user\UserHelper;
use core\models\user\User;
use core\repositories\user\UserRepository;
use core\services\TransactionManager;

class UserService
{
    private $userRepository;
    private $transactionManager;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        UserRepository $userRepository,
        TransactionManager $transactionManager
    )
    {
        $this->userRepository = $userRepository;
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param $company_id
     * @param $password
     * @param $role
     * @param $status
     * @param $username
     * @param $user_permissions
     *
     * @return User
     * @throws \Exception
     */
    public function create($company_id, $password, $role, $status, $username, $user_permissions)
    {
        self::guardUsername($username);

        $user = User::add($company_id, $username, $password, $role);
        $user->setStatus($status);

        $accesses = $this->getAccesses($user, $user_permissions);

        $this->transactionManager->execute(function () use ($user, $accesses) {
            $user->sendPassword($user->getFullName(), $user->password);
            $this->userRepository->add($user);
            if (!empty($accesses)) {
                $user->setAccesses($accesses);
            }
        });

        UserHelper::invalidateMainMenuCache($user->id);

        return $user;
    }

    /**
     * @param $id
     * @param $company_id
     * @param $password
     * @param $role
     * @param $status
     * @param $username
     * @param $user_permissions
     * @return User
     * @throws \Exception
     */
    public function edit($id, $company_id, $password, $role, $status, $username, $user_permissions)
    {
        self::guardUsername($username);

        $user = $this->userRepository->find($id);

        $user->edit($company_id, $password, $role, $status, $username);
        $accesses = $this->getAccesses($user, $user_permissions);

        $this->transactionManager->execute(function () use ($user, $accesses) {
            $this->userRepository->edit($user);
            if (!empty($accesses)) {
                $user->setAccesses($accesses);
            }
        });

        UserHelper::invalidateMainMenuCache($user->id);

        return $user;
    }

    /**
     * @param $id
     * @param $password
     * @return User
     * @throws \Exception
     */
    public function editPassword($id, $password)
    {
        self::guardPassword($password);

        $user = $this->userRepository->find($id);
        $user->editPassword($password);
        $this->userRepository->edit($user);

        return $user;
    }

    /**
     * Get accesses
     */
    private function getAccesses($user, $user_permissions)
    {
        $accesses = [];
        if (!empty($user->role)) {
            $accesses = [$user->role];
        }
        if (!empty($user_permissions)) {
            $accesses = array_merge($accesses, $user_permissions);
        }
        return $accesses;
    }

    /**
     * @param $username
     */
    private function guardUsername($username)
    {
        if (empty($username)) {
            throw new \DomainException('Username not set');
        }
        if (!preg_match("/^\+[0-9] [0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}$/i", $username)) {
            throw new \DomainException('Invalid username format');
        }
    }

    /**
     * @param $password
     */
    private function guardPassword($password)
    {
        if (empty($password)) {
            throw new \DomainException('Password not set');
        }
        if (strlen($password) < 6) {
            throw new \DomainException('Too short password');
        }
    }

    /**
     * @param int $id
     * @param string $key
     */
    public function addPushKey(int $id, string $key)
    {
        $user = $this->userRepository->find($id);
        $user->device_key = $key;
        $this->userRepository->edit($user);
    }
}
