<?php

namespace core\services\user;

use core\models\user\UserLog;
use core\repositories\user\UserRepository;

class Logger
{
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * Logger constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->users = $userRepository;
    }

    /**
     * @param string $username
     * @param int $action
     * @param string $ip
     * @param string $agent
     */
    public function create(string $username, int $action, $ip, $agent)
    {
        $user = $this->users->findByUsername($username);

        $log = new UserLog([
            'action'     => $action,
            'ip_address' => (string) $ip,
            'user_agent' => (string) $agent,
            'datetime'   => date("Y-m-d H:i:s"),
            'user_id'    => $user->id
        ]);

        $log->save(false);
    }

}