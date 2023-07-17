<?php

use yii\db\Migration;

class m171023_074347_alter_auth_rules extends Migration
{
    public function safeUp()
    {
        $this->update('{{%auth_rule}}',
            ['data' => 'O:23:"core\rbac\UserOwnerRule":3:{s:4:"name";s:13:"userUpdateOwn";s:9:"createdAt";i:1451061682;s:9:"updatedAt";i:1451061682;}'],
            ['name' => 'userOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:34:"core\rbac\CompanyCustomerOwnerRule":3:{s:4:"name";s:20:"companyCustomerOwner";s:9:"createdAt";i:1467966927;s:9:"updatedAt";i:1467966927;}'],
            ['name' => 'companyCustomerOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:24:"core\rbac\OrderOwnerRule":3:{s:4:"name";s:10:"orderOwner";s:9:"createdAt";i:1467966927;s:9:"updatedAt";i:1467966927;}'],
            ['name' => 'orderOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:34:"core\rbac\DivisionServiceOwnerRule":3:{s:4:"name";s:20:"divisionServiceOwner";s:9:"createdAt";i:1467966927;s:9:"updatedAt";i:1467966927;}'],
            ['name' => 'divisionServiceOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:26:"core\rbac\CompanyOwnerRule":3:{s:4:"name";s:12:"companyOwner";s:9:"createdAt";i:1467967321;s:9:"updatedAt";i:1467967321;}'],
            ['name' => 'companyOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:23:"core\rbac\CashOwnerRule":3:{s:4:"name";s:9:"cashOwner";s:9:"createdAt";i:1467967321;s:9:"updatedAt";i:1467967321;}'],
            ['name' => 'cashOwner']
        );
    }

    public function safeDown()
    {
        $this->update('{{%auth_rule}}',
            ['data' => 'O:22:"app\rbac\UserOwnerRule":3:{s:4:"name";s:13:"userUpdateOwn";s:9:"createdAt";i:1451061682;s:9:"updatedAt";i:1451061682;}'],
            ['name' => 'userOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:33:"app\rbac\CompanyCustomerOwnerRule":3:{s:4:"name";s:20:"companyCustomerOwner";s:9:"createdAt";i:1467966927;s:9:"updatedAt";i:1467966927;}'],
            ['name' => 'companyCustomerOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:23:"app\rbac\OrderOwnerRule":3:{s:4:"name";s:10:"orderOwner";s:9:"createdAt";i:1467966927;s:9:"updatedAt";i:1467966927;}'],
            ['name' => 'orderOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:33:"app\rbac\DivisionServiceOwnerRule":3:{s:4:"name";s:20:"divisionServiceOwner";s:9:"createdAt";i:1467966927;s:9:"updatedAt";i:1467966927;}'],
            ['name' => 'divisionServiceOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:25:"app\rbac\CompanyOwnerRule":3:{s:4:"name";s:12:"companyOwner";s:9:"createdAt";i:1467967321;s:9:"updatedAt";i:1467967321;}'],
            ['name' => 'companyOwner']
        );

        $this->update('{{%auth_rule}}',
            ['data' => 'O:22:"app\rbac\CashOwnerRule":3:{s:4:"name";s:9:"cashOwner";s:9:"createdAt";i:1467967321;s:9:"updatedAt";i:1467967321;}'],
            ['name' => 'cashOwner']
        );
    }
}
