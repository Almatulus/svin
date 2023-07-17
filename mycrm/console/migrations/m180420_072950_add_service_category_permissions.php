<?php

use yii\db\Migration;

/**
 * Class m180420_072950_add_service_category_permissions
 */
class m180420_072950_add_service_category_permissions extends Migration
{
    private $data = [
        'serviceCategoryAdmin' => [
            'serviceCategoryCreate' => null,
            'serviceCategoryUpdate' => \core\rbac\BaseOwnerRule::class,
            'serviceCategoryDelete' => \core\rbac\BaseOwnerRule::class
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $authManager = Yii::$app->authManager;

        $users = $authManager->getUserIdsByRole('divisionServiceView');

        foreach ($this->data as $parentPermissionName => $permissions) {
            $parentPermission = $authManager->createPermission($parentPermissionName);
            $authManager->add($parentPermission);
            foreach ($permissions as $permissionName => $ruleClass) {
                $childPermission = $authManager->createPermission($permissionName);
                if ($ruleClass) {
                    $ruleModel = new $ruleClass();
                    if (!$authManager->getRule($ruleModel->name)) {
                        $authManager->add($ruleModel);
                    }
                    $childPermission->ruleName = $ruleModel->name;
                }
                $authManager->add($childPermission);
                $authManager->addChild($parentPermission, $childPermission);

                foreach ($users as $user_id) {
                    $authManager->assign($childPermission, $user_id);
                }
            }

            $administrator = Yii::$app->authManager->getRole('administrator');
            $company = Yii::$app->authManager->getRole('company');
            $authManager->addChild($administrator, $parentPermission);
            $authManager->addChild($company, $parentPermission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $authManager = Yii::$app->authManager;
        foreach ($this->data as $parentPermissionName => $permissions) {
            $parentPermission = $authManager->createPermission($parentPermissionName);
            $authManager->remove($parentPermission);
            foreach ($permissions as $permissionName => $ruleClass) {
                $permission = $authManager->createPermission($permissionName);
                $authManager->remove($permission);
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180420_072950_add_service_category_permissions cannot be reverted.\n";

        return false;
    }
    */
}
