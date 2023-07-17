<?php

namespace core\rbac;

trait RbacPermissions
{
    /**
     * Returns create model permission name
     *
     * @return string
     */
    public static function getCreatePermissionName()
    {
        return self::getPermissionKey() . 'Create';
    }

    /**
     * Returns update model permission name
     *
     * @return string
     */
    public static function getUpdatePermissionName()
    {
        return self::getPermissionKey() . 'Update';
    }

    /**
     * Returns delete model permission name
     *
     * @return string
     */
    public static function getDeletePermissionName()
    {
        return self::getPermissionKey() . 'Delete';
    }

    /**
     * Returns view model permission name
     *
     * @return string
     */
    public static function getViewPermissionName()
    {
        return self::getPermissionKey() . 'View';
    }

    /**
     * Returns view own model permission name
     *
     * @return string
     */
    public static function getViewOwnPermissionName()
    {
        return self::getPermissionKey() . 'ViewOwn';
    }

    /**
     * Returns view own model permission name
     *
     * @return string
     */
    public static function getUpdateOwnPermissionName()
    {
        return self::getPermissionKey() . 'UpdateOwn';
    }

    /**
     * Returns admin model permission name
     *
     * @return string
     */
    public static function getAdminPermissionName()
    {
        return self::getPermissionKey() . 'Admin';
    }
}