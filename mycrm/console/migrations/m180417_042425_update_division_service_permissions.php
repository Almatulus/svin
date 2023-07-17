<?php

use yii\db\Migration;

/**
 * Class m180417_042425_update_division_service_permissions
 */
class m180417_042425_update_division_service_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $permission = $auth->getPermission('divisionServiceOwner');
        $children = $auth->getChildren('divisionServiceOwner');

        $users = $auth->getUserIdsByRole('divisionServiceOwner');

        foreach ($users as $user_id) {
            $auth->revoke($permission, $user_id);

            foreach ($children as $child) {
                $auth->assign($child, $user_id);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $permission = $auth->getPermission('divisionServiceOwner');
        $children = $auth->getChildren('divisionServiceOwner');

        $users = $auth->getUserIdsByRole('divisionServiceDeleteOwn');

        foreach ($users as $user_id) {
            $auth->assign($permission, $user_id);

            foreach ($children as $child) {
                $auth->revoke($child, $user_id);
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
        echo "m180417_042425_update_division_service_permissions cannot be reverted.\n";

        return false;
    }
    */
}
