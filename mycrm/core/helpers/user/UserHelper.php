<?php

namespace core\helpers\user;

use core\helpers\MenuList;
use core\models\user\User;
use Yii;
use yii\helpers\ArrayHelper;

class UserHelper
{
    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            User::STATUS_ENABLED  => Yii::t('app', 'enabled'),
            User::STATUS_DISABLED => Yii::t('app', 'disabled'),
        ];
    }

    /**
     * @param integer $user_id
     *
     * @return string
     */
    public static function getMainMenuCacheKey($user_id)
    {
        return YII_ENV . '_MAIN_MENU_' . $user_id;
    }

    /**
     * @param int $user_id
     *
     * @return array
     */
    public static function invalidateMainMenuCache(int $user_id)
    {
        try {
            $modules = MenuList::modules();

            $menu_items = [];
            foreach ($modules as $moduleKey) {
                $permissions = MenuList::permissions()[$moduleKey];
                $user_permissions = ArrayHelper::getColumn(Yii::$app->authManager->getPermissionsByUser($user_id),
                    'name');

                if (is_bool($permissions) && $permissions) {
                    $menu_items[$moduleKey] = [$moduleKey];
                } else {
                    foreach ($permissions as $permissionName) {
                        if (isset($user_permissions[$permissionName])) {
                            $permissionName = lcfirst(preg_replace("/(company)|(View)|(Admin)/", "", $permissionName));
                            $menu_items[$moduleKey][] = $permissionName;
                        }
                    }
                }
            }

            $key = UserHelper::getMainMenuCacheKey($user_id);
            Yii::$app->cache->set($key, $menu_items);

            return $menu_items;
        } catch (\Exception $e) {
        }
    }
}
