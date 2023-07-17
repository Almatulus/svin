<?php

use core\models\company\Company;
use core\models\finance\CompanyCostItem;
use yii\db\Migration;

/**
 * Class m180402_045451_drop_order_items_constraints
 */
class m180402_045451_drop_order_items_constraints extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropIndex('uq_company_cashflow_products_order_product', '{{%company_cashflow_products}}');
        $this->dropIndex('uq_company_cashflow_services_order_service', '{{%company_cashflow_services}}');

        $companies = Company::find()->select(['id'])->asArray();

        foreach ($companies->each(100) as $companyData) {
            $this->insert('{{%company_cost_items}}', [
                'company_id'     => $companyData['id'],
                'name'           => 'COST ITEM EXPENSE REFUND',
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_REFUND,
                'is_deletable'   => false
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->createIndex('uq_company_cashflow_products_order_product', '{{%company_cashflow_products}}',
            'order_service_product_id', true);
        $this->createIndex('uq_company_cashflow_services_order_service', '{{%company_cashflow_services}}',
            'order_service_id', true);

        $this->delete('{{%company_cost_items}}', [
            'type'           => CompanyCostItem::TYPE_EXPENSE,
            'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_REFUND,
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180402_045451_drop_order_items_constraints cannot be reverted.\n";

        return false;
    }
    */
}
