<?php

use core\models\user\User;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * @param mixed $var
     * @return User
     */
    public function login($var = [])
    {
        $user = $var instanceof User ? $var : $this->getFactory()->create(User::class, $var);
        $this->amBearerAuthenticated($user->authKey);

        return $user;
    }

    public function checkLogin($url)
    {
        $this->sendPOST($url);
        $this->seeResponseCodeIs(401);
    }

    public function seeAccessDenied($url)
    {
        $this->sendPOST($url);
        $this->seeResponseCodeIs(403);
    }

    public function assignRoles($user, $role)
    {
        $auth = Yii::$app->authManager;
        $authorRole = $auth->getRole($role);
        $auth->assign($authorRole, $user->getId());
    }

    public function assignPermission($user, $permission)
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission($permission);
        $auth->assign($permission, $user->getId());
    }
}
