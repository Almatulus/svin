<?php
namespace core\rbac;

use core\models\user\User;
use yii\rbac\Rule;
use yii\rbac\Item;

/**
 * Checks if authorID matches user passed via params
 */
class CompanyOwnerRule extends Rule
{
    public $name = 'companyOwner';

    /**
     * @param string|integer $user_id the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user_id, $item, $params)
    {
        $user = User::findOne($user_id);
        return isset($params['model']) && $user ? $params['model']->id == $user->company_id : false;
    }
}
