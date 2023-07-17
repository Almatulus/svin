<?php

namespace core\rbac;

interface IRbacPermissions
{
    /**
     * Returns key name for permissions
     *
     * @return string
     */
    public static function getPermissionKey();

    /**
     * Returns create model permission name
     *
     * @return string
     */
    public static function getCreatePermissionName();

    /**
     * Returns update model permission name
     *
     * @return string
     */
    public static function getUpdatePermissionName();

    /**
     * Returns delete model permission name
     *
     * @return string
     */
    public static function getDeletePermissionName();

    /**
     * Returns view model permission name
     *
     * @return string
     */
    public static function getViewPermissionName();

    /**
     * Returns view own model permission name
     *
     * @return string
     */
    public static function getViewOwnPermissionName();

    /**
     * Returns view own model permission name
     *
     * @return string
     */
    public static function getUpdateOwnPermissionName();

    /**
     * Returns admin model permission name
     *
     * @return string
     */
    public static function getAdminPermissionName();
}