<?php

use yii\db\Migration;

class m160327_164327_create_table_cashflow extends Migration
{
    public function safeUp()
    {
        $this->createTable('crm_company_cashflows', [
            'id' => $this->primaryKey(),
            'date' => $this->dateTime()->notNull(),
            'cost_item_id' => $this->integer()->notNull(),
            'cash_id' => $this->integer()->notNull(),
            'receiver_mode' => $this->smallInteger()->notNull()->defaultValue(0),
                'contractor_id' => $this->integer(),
                'customer_id' => $this->integer(),
                'staff_id' => $this->integer(),
            'value' => $this->integer()->notNull()->defaultValue(0),
            'comment' => $this->text(),
            'user_id' => $this->integer()->notNull(),
            'company_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk_company','crm_company_cashflows','company_id','crm_companies','id');

        $this->addForeignKey('fk_cost_item','crm_company_cashflows','cost_item_id','crm_company_cost_items','id');
        $this->addForeignKey('fk_cash','crm_company_cashflows','cash_id','crm_company_cashes','id');

        $this->addForeignKey('fk_contractor','crm_company_cashflows','contractor_id','crm_company_contractors','id');
        $this->addForeignKey('fk_customer','crm_company_cashflows','customer_id','crm_company_customers','id');
        $this->addForeignKey('fk_staff','crm_company_cashflows','staff_id','crm_staffs','id');

        $this->addForeignKey('fk_user','crm_company_cashflows','user_id','crm_users','id');
    }

    public function safeDown()
    {
        $this->dropTable('crm_company_cashflows');
    }
}
