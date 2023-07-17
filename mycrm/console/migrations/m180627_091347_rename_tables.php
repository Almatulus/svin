<?php

use yii\db\Migration;

/**
 * Class m180627_091347_rename_tables
 */
class m180627_091347_rename_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropPrimaryKey('crm_company_cashflow_products_pkey', '{{%company_cashflow_products}}');
        $this->dropPrimaryKey('crm_company_cashflow_services_pkey', '{{%company_cashflow_services}}');
        $this->dropPrimaryKey('sale_product_cashflow_pk', '{{%sale_product_cashflow}}');

        $this->dropForeignKey('fk_company_cashflow_services_cashflow', '{{%company_cashflow_products}}');
        $this->dropForeignKey('fk_company_cashflow_services_cashflow', '{{%company_cashflow_services}}');
        $this->dropForeignKey('fk_sale_product_cashflow_cashflow', '{{%sale_product_cashflow}}');

        \core\models\finance\CompanyCashflowProduct::deleteAll("cashflow_id  IN(SELECT cashflow_id FROM {{%sale_product_cashflow}})");

        \core\models\finance\CompanyCashflowPayment::deleteAll("cashflow_id  IN(SELECT cashflow_id FROM {{%company_cashflow_products}})");
        \core\models\finance\CompanyCashflowPayment::deleteAll("cashflow_id IN(SELECT cashflow_id FROM {{%company_cashflow_services}})");
        \core\models\finance\CompanyCashflowPayment::deleteAll("cashflow_id  IN(SELECT cashflow_id FROM {{%sale_product_cashflow}})");

        \core\models\finance\CompanyCashflow::deleteAll("id IN (SELECT cashflow_id FROM {{%company_cashflow_products}})");
        \core\models\finance\CompanyCashflow::deleteAll("id IN (SELECT cashflow_id FROM {{%company_cashflow_services}})");
        \core\models\finance\CompanyCashflow::deleteAll("id IN (SELECT cashflow_id FROM {{%sale_product_cashflow}})");

        $this->dropTable('{{%company_cashflow_products}}');
        $this->dropTable('{{%company_cashflow_services}}');
        $this->dropTable('{{%sale_product_cashflow}}');

        $this->renameTable('{{%cashflow_products}}', '{{%company_cashflow_products}}');
        $this->renameTable('{{%cashflow_services}}', '{{%company_cashflow_services}}');
        $this->renameTable('{{%cashflow_orders}}', '{{%company_cashflow_orders}}');
        $this->renameTable('{{%cashflow_sales}}', '{{%company_cashflow_sales}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('{{%company_cashflow_products}}', '{{%cashflow_products}}');
        $this->renameTable('{{%company_cashflow_services}}', '{{%cashflow_services}}');
        $this->renameTable('{{%company_cashflow_orders}}', '{{%cashflow_orders}}');
        $this->renameTable('{{%company_cashflow_sales}}', '{{%cashflow_sales}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180627_091347_rename_tables cannot be reverted.\n";

        return false;
    }
    */
}
