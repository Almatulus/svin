<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace console\controllers;

use core\models\medCard\MedCardComment;
use core\models\medCard\MedCardCommentCategory;
use core\rbac\CashOwnerRule;
use core\rbac\CompanyCustomerOwnerRule;
use core\rbac\CompanyOwnerRule;
use core\rbac\DivisionServiceOwnerRule;
use core\rbac\IRbacPermissions;
use core\rbac\OrderOwnerRule;
use yii\base\Module;
use yii\console\Controller;
use Yii;
use yii\rbac\Role;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RbacController extends Controller
{
    /* @var \yii\rbac\ManagerInterface $authManager */
    private $authManager;

    /**
     * RbacController constructor.
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        $this->authManager = Yii::$app->authManager;

        parent::__construct($id, $module, $config);
    }

    /**
     * Create system permissions
     */
    public function actionCreate()
    {
        // Customers permissions
//        $companyCustomerCreate = $this->authManager->createPermission('companyCustomerCreate');
//        $this->authManager->add($companyCustomerCreate);
//        $companyCustomerUpdate = $this->authManager->createPermission('companyCustomerUpdate');
//        $this->authManager->add($companyCustomerUpdate);
//        $companyCustomerView = $this->authManager->createPermission('companyCustomerView');
//        $this->authManager->add($companyCustomerView);
//        $companyCustomerDelete = $this->authManager->createPermission('companyCustomerDelete');
//        $this->authManager->add($companyCustomerDelete);
//
//        $companyCustomerAdmin = $this->authManager->createPermission('companyCustomerAdmin');
//        $this->authManager->add($companyCustomerAdmin);
//        $this->authManager->addChild($companyCustomerAdmin, $companyCustomerView);
//        $this->authManager->addChild($companyCustomerAdmin, $companyCustomerUpdate);
//        $this->authManager->addChild($companyCustomerAdmin, $companyCustomerCreate);
//        $this->authManager->addChild($companyCustomerAdmin, $companyCustomerDelete);
//
//        $rule = new CompanyCustomerOwnerRule();
//        $this->authManager->add($rule);
//
//        $companyCustomerUpdateOwn = $this->authManager->createPermission('companyCustomerUpdateOwn');
//        $companyCustomerUpdateOwn->ruleName = $rule->name;
//        $this->authManager->add($companyCustomerUpdateOwn);
//        $this->authManager->addChild($companyCustomerUpdateOwn, $companyCustomerUpdate);
//
//        $companyCustomerDeleteOwn = $this->authManager->createPermission('companyCustomerDeleteOwn');
//        $companyCustomerDeleteOwn->ruleName = $rule->name;
//        $this->authManager->add($companyCustomerDeleteOwn);
//        $this->authManager->addChild($companyCustomerDeleteOwn, $companyCustomerDelete);
//
//        $companyCustomerViewer = $this->authManager->createPermission('companyCustomerOwner');
//        $this->authManager->add($companyCustomerViewer);
//        $this->authManager->addChild($companyCustomerViewer, $companyCustomerView);
//        $this->authManager->addChild($companyCustomerViewer, $companyCustomerUpdateOwn);
//        $this->authManager->addChild($companyCustomerViewer, $companyCustomerCreate);
//        $this->authManager->addChild($companyCustomerViewer, $companyCustomerDeleteOwn);
//
//        // Order permissions
//        $orderCreate = $this->authManager->createPermission('orderCreate');
//        $this->authManager->add($orderCreate);
//        $orderUpdate = $this->authManager->createPermission('orderUpdate');
//        $this->authManager->add($orderUpdate);
//        $orderView = $this->authManager->createPermission('orderView');
//        $this->authManager->add($orderView);
//        $orderDelete = $this->authManager->createPermission('orderDelete');
//        $this->authManager->add($orderDelete);
//
//        $orderAdmin = $this->authManager->createPermission('orderAdmin');
//        $this->authManager->add($orderAdmin);
//        $this->authManager->addChild($orderAdmin, $orderView);
//        $this->authManager->addChild($orderAdmin, $orderUpdate);
//        $this->authManager->addChild($orderAdmin, $orderCreate);
//        $this->authManager->addChild($orderAdmin, $orderDelete);
//
//        $rule = new OrderOwnerRule();
//        $this->authManager->add($rule);
//
//        $orderUpdateOwn = $this->authManager->createPermission('orderUpdateOwn');
//        $orderUpdateOwn->ruleName = $rule->name;
//        $this->authManager->add($orderUpdateOwn);
//        $this->authManager->addChild($orderUpdateOwn, $orderUpdate);
//
//        $orderDeleteOwn = $this->authManager->createPermission('orderDeleteOwn');
//        $orderDeleteOwn->ruleName = $rule->name;
//        $this->authManager->add($orderDeleteOwn);
//        $this->authManager->addChild($orderDeleteOwn, $orderDelete);
//
//        $orderViewer = $this->authManager->createPermission('orderOwner');
//        $this->authManager->add($orderViewer);
//        $this->authManager->addChild($orderViewer, $orderView);
//        $this->authManager->addChild($orderViewer, $orderUpdateOwn);
//        $this->authManager->addChild($orderViewer, $orderCreate);
//        $this->authManager->addChild($orderViewer, $orderDeleteOwn);
//
//        // Division service permissions
//        $divisionServiceCreate = $this->authManager->createPermission('divisionServiceCreate');
//        $this->authManager->add($divisionServiceCreate);
//        $divisionServiceUpdate = $this->authManager->createPermission('divisionServiceUpdate');
//        $this->authManager->add($divisionServiceUpdate);
//        $divisionServiceView = $this->authManager->createPermission('divisionServiceView');
//        $this->authManager->add($divisionServiceView);
//        $divisionServiceDelete = $this->authManager->createPermission('divisionServiceDelete');
//        $this->authManager->add($divisionServiceDelete);
//
//        $divisionServiceAdmin = $this->authManager->createPermission('divisionServiceAdmin');
//        $this->authManager->add($divisionServiceAdmin);
//        $this->authManager->addChild($divisionServiceAdmin, $divisionServiceView);
//        $this->authManager->addChild($divisionServiceAdmin, $divisionServiceUpdate);
//        $this->authManager->addChild($divisionServiceAdmin, $divisionServiceCreate);
//        $this->authManager->addChild($divisionServiceAdmin, $divisionServiceDelete);
//
//        $rule = new DivisionServiceOwnerRule();
//        $this->authManager->add($rule);
//
//        $divisionServiceUpdateOwn = $this->authManager->createPermission('divisionServiceUpdateOwn');
//        $divisionServiceUpdateOwn->ruleName = $rule->name;
//        $this->authManager->add($divisionServiceUpdateOwn);
//        $this->authManager->addChild($divisionServiceUpdateOwn, $divisionServiceUpdate);
//
//        $divisionServiceDeleteOwn = $this->authManager->createPermission('divisionServiceDeleteOwn');
//        $divisionServiceDeleteOwn->ruleName = $rule->name;
//        $this->authManager->add($divisionServiceDeleteOwn);
//        $this->authManager->addChild($divisionServiceDeleteOwn, $divisionServiceDelete);
//
//        $divisionServiceViewer = $this->authManager->createPermission('divisionServiceOwner');
//        $this->authManager->add($divisionServiceViewer);
//        $this->authManager->addChild($divisionServiceViewer, $divisionServiceView);
//        $this->authManager->addChild($divisionServiceViewer, $divisionServiceUpdateOwn);
//        $this->authManager->addChild($divisionServiceViewer, $divisionServiceCreate);
//        $this->authManager->addChild($divisionServiceViewer, $divisionServiceDeleteOwn);
//
//        // Company permissions
//        $companyCreate = $this->authManager->createPermission('companyCreate');
//        $this->authManager->add($companyCreate);
//        $companyUpdate = $this->authManager->createPermission('companyUpdate');
//        $this->authManager->add($companyUpdate);
//        $companyView = $this->authManager->createPermission('companyView');
//        $this->authManager->add($companyView);
//        $companyDelete = $this->authManager->createPermission('companyDelete');
//        $this->authManager->add($companyDelete);
//
//        $companyAdmin = $this->authManager->createPermission('companyAdmin');
//        $this->authManager->add($companyAdmin);
//        $this->authManager->addChild($companyAdmin, $companyView);
//        $this->authManager->addChild($companyAdmin, $companyUpdate);
//        $this->authManager->addChild($companyAdmin, $companyCreate);
//        $this->authManager->addChild($companyAdmin, $companyDelete);
//
//        $rule = new CompanyOwnerRule();
//        $this->authManager->add($rule);
//
//        $companyUpdateOwn = $this->authManager->createPermission('companyUpdateOwn');
//        $companyUpdateOwn->ruleName = $rule->name;
//        $this->authManager->add($companyUpdateOwn);
//        $this->authManager->addChild($companyUpdateOwn, $companyUpdate);
//
//        $companyDeleteOwn = $this->authManager->createPermission('companyDeleteOwn');
//        $companyDeleteOwn->ruleName = $rule->name;
//        $this->authManager->add($companyDeleteOwn);
//        $this->authManager->addChild($companyDeleteOwn, $companyDelete);
//
//        $companyViewer = $this->authManager->createPermission('companyOwner');
//        $this->authManager->add($companyViewer);
//        $this->authManager->addChild($companyViewer, $companyView);
//        $this->authManager->addChild($companyViewer, $companyUpdateOwn);
//        $this->authManager->addChild($companyViewer, $companyCreate);
//        $this->authManager->addChild($companyViewer, $companyDeleteOwn);
//
//        // Cash permissions
//        $cashCreate = $this->authManager->createPermission('cashCreate');
//        $this->authManager->add($cashCreate);
//        $cashUpdate = $this->authManager->createPermission('cashUpdate');
//        $this->authManager->add($cashUpdate);
//        $cashView = $this->authManager->createPermission('cashView');
//        $this->authManager->add($cashView);
//        $cashDelete = $this->authManager->createPermission('cashDelete');
//        $this->authManager->add($cashDelete);
//
//        $cashAdmin = $this->authManager->createPermission('cashAdmin');
//        $this->authManager->add($cashAdmin);
//        $this->authManager->addChild($cashAdmin, $cashView);
//        $this->authManager->addChild($cashAdmin, $cashUpdate);
//        $this->authManager->addChild($cashAdmin, $cashCreate);
//        $this->authManager->addChild($cashAdmin, $cashDelete);
//
//        $rule = new CashOwnerRule();
//        $this->authManager->add($rule);
//
//        $cashUpdateOwn = $this->authManager->createPermission('cashUpdateOwn');
//        $cashUpdateOwn->ruleName = $rule->name;
//        $this->authManager->add($cashUpdateOwn);
//        $this->authManager->addChild($cashUpdateOwn, $cashUpdate);
//
//        $cashDeleteOwn = $this->authManager->createPermission('cashDeleteOwn');
//        $cashDeleteOwn->ruleName = $rule->name;
//        $this->authManager->add($cashDeleteOwn);
//        $this->authManager->addChild($cashDeleteOwn, $cashDelete);
//
//        $cashViewer = $this->authManager->createPermission('cashOwner');
//        $this->authManager->add($cashViewer);
//        $this->authManager->addChild($cashViewer, $cashView);
//        $this->authManager->addChild($cashViewer, $cashUpdateOwn);
//        $this->authManager->addChild($cashViewer, $cashCreate);
//        $this->authManager->addChild($cashViewer, $cashDeleteOwn);

        $this->setupPermission(new MedCardCommentCategory());
        $this->setupPermission(new MedCardComment());

        $administrator = $this->authManager->getRole('administrator');
        $this->addAdminPermission($administrator, new MedCardCommentCategory());
        $this->addAdminPermission($administrator, new MedCardComment());

        echo "Permissions are updated\n";
    }

    /**
     * Adds admin child permission to a role
     *
     * @param Role $parent
     * @param IRbacPermissions $modelPermission
     */
    private function addAdminPermission($parent, IRbacPermissions $modelPermission)
    {
        $adminPermission = $this->authManager->getPermission($modelPermission::getAdminPermissionName());

        if ($this->authManager->canAddChild($parent, $adminPermission)) {
            $this->authManager->addChild($parent, $adminPermission);
        }
    }

    /**
     * Generate permissions
     *
     * @param IRbacPermissions $modelPermission
     */
    private function setupPermission(IRbacPermissions $modelPermission)
    {
        $createPermission = $this->authManager->getPermission($modelPermission::getCreatePermissionName());
        if ($createPermission == null) {
            $createPermission = $this->authManager->createPermission($modelPermission::getCreatePermissionName());
            $this->authManager->add($createPermission);
        }

        $updatePermission = $this->authManager->getPermission($modelPermission::getUpdatePermissionName());
        if ($updatePermission === null) {
            $updatePermission = $this->authManager->createPermission($modelPermission::getUpdatePermissionName());
            $this->authManager->add($updatePermission);
        }

        $viewPermission = $this->authManager->getPermission($modelPermission::getViewPermissionName());
        if ($viewPermission === null) {
            $viewPermission = $this->authManager->createPermission($modelPermission::getViewPermissionName());
            $this->authManager->add($viewPermission);
        }

        $deletePermission = $this->authManager->getPermission($modelPermission::getDeletePermissionName());
        if ($deletePermission === null) {
            $deletePermission = $this->authManager->createPermission($modelPermission::getDeletePermissionName());
            $this->authManager->add($deletePermission);
        }

        $adminPermission = $this->authManager->getPermission($modelPermission::getAdminPermissionName());
        if ($adminPermission === null) {
            $adminPermission = $this->authManager->createPermission($modelPermission::getAdminPermissionName());
            $this->authManager->add($adminPermission);
            $this->authManager->addChild($adminPermission, $viewPermission);
            $this->authManager->addChild($adminPermission, $updatePermission);
            $this->authManager->addChild($adminPermission, $createPermission);
            $this->authManager->addChild($adminPermission, $deletePermission);
        }
    }
}
