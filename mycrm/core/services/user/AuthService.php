<?php

namespace core\services\user;

use core\models\ConfirmKey;
use core\models\customer\CustomerRequest;
use core\repositories\ConfirmKeyRepository;
use core\repositories\user\UserRepository;
use core\services\TransactionManager;

class AuthService
{
    private $userRepository;
    private $transactionManager;
    private $confirmKeyRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepository       $userRepository
     * @param ConfirmKeyRepository $confirmKeyRepository
     * @param TransactionManager   $transactionManager
     */
    public function __construct(
        UserRepository $userRepository,
        ConfirmKeyRepository $confirmKeyRepository,
        TransactionManager $transactionManager
    ) {
        $this->userRepository       = $userRepository;
        $this->transactionManager   = $transactionManager;
        $this->confirmKeyRepository = $confirmKeyRepository;
    }

    /**
     * Generates new or uses existing access token
     *
     * @param string $username
     *
     * @return string
     * @throws \Exception
     */
    public function getAccessToken($username)
    {
        $user = $this->userRepository->findByUsername($username);
        if ( ! $user->hasValidToken()) {
            $user->generateToken();
            $this->transactionManager->execute(function () use ($user) {
                $this->userRepository->edit($user);
            });
        }

        return $user->access_token;
    }

    /**
     * @param $user_id
     *
     * @return \core\models\user\User
     * @throws \Exception
     */
    public function logout($user_id)
    {
        $user = $this->userRepository->find($user_id);
        $user->generateToken();
        $user->generateAuthKey();

        $this->transactionManager->execute(function() use ($user){
            $this->userRepository->edit($user);
        });

        return $user;
    }

    /**
     * Generates confirmation key to authorize
     *
     * @param string $username
     *
     * @throws \Exception
     */
    public function sendConfirmKey($username)
    {
        $user = $this->userRepository->findByUsername($username);

        $confirmKey = ConfirmKey::add($user->username);

        $this->transactionManager->execute(function () use ($confirmKey, $user) {
            $this->confirmKeyRepository->save($confirmKey);
            if (!YII_ENV_TEST) {
                CustomerRequest::sendNotAssignedSMS(
                    $confirmKey->username,
                    'Не показывайте код никому ' . $confirmKey->code,
                    $user->company_id
                );
            }
        });
    }

    /**
     * @param string $username
     * @param string $code
     * @param string $password
     *
     * @return string
     * @throws \Exception
     */
    public function changePassword($username, $code, $password)
    {
        $user = $this->userRepository->findByUsername($username);
        $confirmKey = $this->confirmKeyRepository->findActiveByCodeAndUsername($code, $user->username);
        $confirmKey->disable();

        $user->editPassword($password);
        $user->generateToken();

        $this->transactionManager->execute(function () use ($confirmKey, $user) {
            $this->userRepository->edit($user);
            $this->confirmKeyRepository->save($confirmKey);
        });

        return $user->access_token;
    }
}
