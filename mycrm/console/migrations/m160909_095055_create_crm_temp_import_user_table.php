<?php

use yii\db\Migration;

/**
 * Handles the creation for table `crm_temp_import_user`.
 */
class m160909_095055_create_crm_temp_import_user_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('crm_temp_customers', [
            'id' => $this->primaryKey(),
            'address' => $this->text(),
            'birth_date' => $this->date(),
            'categories' => $this->text(),
            'city' => $this->string(),
            'comments' => $this->text(),
            'company_id' => $this->integer(),
            'discount' => $this->integer(),
            'gender' => $this->smallinteger(),
            'email' => $this->string(),
            'name' => $this->string(),
            'lastname' => $this->string(),
            'phone' => $this->string(),
            'sms_birthday' => $this->boolean(),
            'sms_exclude' => $this->boolean(),
        ]);

        $this->addForeignKey('fk_temp_customer_company', 'crm_temp_customers', 'company_id', 'crm_companies', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_temp_customer_company', 'crm_temp_customers');
        $this->dropTable('crm_temp_customers');
    }
}
