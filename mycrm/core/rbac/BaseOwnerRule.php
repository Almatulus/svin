<?php

namespace core\rbac;

use yii\rbac\Item;
use yii\rbac\Rule;

/**
 * This rule have to be applied to models with 'company_id' attribute
 * Class BaseOwnerRule
 * @package core\rbac
 */
class BaseOwnerRule extends Rule
{
    public $name = 'baseOwnerRule';

    /**
     * @param string|integer $user_id the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user_id, $item, $params)
    {
        return true;
    }
}
