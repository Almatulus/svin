<?php

use core\models\rbac\AuthAssignment;
use yii\db\Migration;

/**
 * Class m180107_170917_fix_staff_permissions
 */
class m180107_170917_fix_staff_permissions extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $permissions = [
            'companyCustomerView' => 'companyCustomerOwner',
            'orderView'           => 'orderOwner',
            'companyView'         => 'companyOwner',
            'divisionServiceView' => 'divisionServiceOwner',
        ];

        foreach ($permissions as $old => $new) {
            AuthAssignment::updateAll(['item_name' => $new], ['item_name' => $old]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }
}
