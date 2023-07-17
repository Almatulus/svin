<?php

use yii\db\Migration;
use yii\db\Schema;

class m160317_153332_add_table_company_customers extends Migration
{

    public function safeUp()
    {
        // create crm_company_customers
        $this->createTable('crm_company_customers',[
            'id' => Schema::TYPE_PK,
            'discount' => Schema::TYPE_SMALLINT,
            'rank' => Schema::TYPE_SMALLINT,
            'sms_birthday' => Schema::TYPE_BOOLEAN,
            'sms_exclude' => Schema::TYPE_BOOLEAN,
        ]);

        $this->alterColumn('crm_company_customers','discount','SET NOT NULL');
        $this->alterColumn('crm_company_customers','discount','SET DEFAULT 0');
        $this->alterColumn('crm_company_customers','rank','SET NOT NULL');
        $this->alterColumn('crm_company_customers','rank','SET DEFAULT 0');
        $this->alterColumn('crm_company_customers','sms_birthday','SET NOT NULL');
        $this->alterColumn('crm_company_customers','sms_birthday','SET DEFAULT true');
        $this->alterColumn('crm_company_customers','sms_exclude','SET NOT NULL');
        $this->alterColumn('crm_company_customers','sms_exclude','SET DEFAULT false');

        $this->addColumn('crm_company_customers','customer_id',Schema::TYPE_INTEGER.' NOT NULL');
        $this->addForeignKey('fk_customer','crm_company_customers','customer_id','crm_customers','id');

        $this->addColumn('crm_company_customers','company_id',Schema::TYPE_INTEGER.' NOT NULL');
        $this->addForeignKey('fk_company','crm_company_customers','company_id','crm_companies','id');

        $this->createIndex('crm_unique_customers','crm_company_customers',['company_id','customer_id'],true);

        // remove crm_company_customers columns from crm_customers
        $this->dropColumn('crm_customers','discount');
        $this->dropColumn('crm_customers','rank');
        $this->dropColumn('crm_customers','sms_birthday');
        $this->dropColumn('crm_customers','sms_exclude');
        $this->dropForeignKey('fk_company','crm_customers');
        $this->dropColumn('crm_customers','company_id');

        // edit crm_customer_category_map
        $this->dropForeignKey('fk_customer','crm_customer_category_map');
        $this->dropColumn('crm_customer_category_map','customer_id');
        $this->delete('crm_customer_category_map');
        $this->addColumn('crm_customer_category_map','company_customer_id',Schema::TYPE_INTEGER.' NOT NULL');
        $this->addForeignKey('fk_company_customer','crm_customer_category_map','company_customer_id','crm_company_customers','id');
        $this->renameTable('crm_customer_category_map','crm_company_customer_category_map');

        // add customer_id to crm_customer_requests
        $this->addColumn('crm_customer_requests','company_id',Schema::TYPE_INTEGER);
        $this->addForeignKey('fk_company','crm_customer_requests','company_id','crm_companies','id');

        // relink (customer_id)crm_orders to (company_customer_id)crm_company_customers
        $this->dropForeignKey('crm_orders_customer','crm_orders');
        $this->dropColumn('crm_orders','customer_id');
        $this->delete('crm_orders');
        $this->addColumn('crm_orders','company_customer_id',Schema::TYPE_INTEGER.' NOT NULL');
        $this->addForeignKey('fk_company_customer','crm_orders','company_customer_id','crm_company_customers','id');
    }

    public function safeDown()
    {
    }
}
