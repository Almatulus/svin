<?php
namespace core\rbac;

use core\models\user\User;
use yii\rbac\Rule;
use yii\rbac\Item;

/**
 * Checks if author company_id matches user passed via params
 */
class CashOwnerRule extends Rule
{
    public $name = 'cashOwner';

    /**
     * @param integer $user_id the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user_id, $item, $params)
    {
        /* @var User $user */
        $user = User::find()->where(['id' => $user_id])->one();
        return isset($params['model']) && $user ? $params['model']->company_id == $user->company_id : false;
    }
}
