<?php

use yii\db\Migration;

class m161031_043832_create_division_permissions extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Division permissions
        $divisionCreate = $auth->createPermission('divisionCreate');
        $auth->add($divisionCreate);
        $divisionUpdate = $auth->createPermission('divisionUpdate');
        $auth->add($divisionUpdate);
        $divisionView = $auth->createPermission('divisionView');
        $auth->add($divisionView);
        $divisionDelete = $auth->createPermission('divisionDelete');
        $auth->add($divisionDelete);

        $divisionAdmin = $auth->createPermission('divisionAdmin');
        $auth->add($divisionAdmin);
        $auth->addChild($divisionAdmin, $divisionView);
        $auth->addChild($divisionAdmin, $divisionUpdate);
        $auth->addChild($divisionAdmin, $divisionCreate);
        $auth->addChild($divisionAdmin, $divisionDelete);

        $administrator = Yii::$app->authManager->getRole('administrator');
        $auth->addChild($administrator, $divisionAdmin);
    }

    public function safeDown()
    {
    }
}
