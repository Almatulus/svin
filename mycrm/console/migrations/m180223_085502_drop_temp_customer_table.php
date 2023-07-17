<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `temp_customer`.
 */
class m180223_085502_drop_temp_customer_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropTable('{{%temp_customers}}');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->createTable('{{%temp_customers}}', [
            'id' => $this->primaryKey(),
            'address' => $this->text(),
            'birth_date' => $this->date(),
            'categories' => $this->text(),
            'city' => $this->string(),
            'comments' => $this->text(),
            'company_id' => $this->integer()->unsigned(),
            'discount' => $this->integer()->unsigned(),
            'gender' => $this->integer()->unsigned(),
            'email' => $this->string(),
            'name' => $this->string(),
            'lastname' => $this->string(),
            'phone' => $this->string(),
            'sms_birthday' => $this->boolean(),
            'sms_exclude' => $this->boolean()
        ]);
    }
}
