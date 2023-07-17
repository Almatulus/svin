<?php

use core\models\company\Company;
use core\models\finance\CompanyCostItem;
use yii\db\Migration;

/**
 * Class m180411_110509_add_cash_transfer_cost_item
 */
class m180411_110509_add_cash_transfer_cost_item extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $companies = Company::find()->select(['id'])->asArray();

        foreach ($companies->each(100) as $companyData) {
            $this->insert('{{%company_cost_items}}', [
                'company_id'     => $companyData['id'],
                'name'           => 'COST ITEM INCOME CASH TRANSFER',
                'type'           => CompanyCostItem::TYPE_INCOME,
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_INCOME_CASH_TRANSFER,
                'is_deletable'   => false
            ]);

            $this->insert('{{%company_cost_items}}', [
                'company_id'     => $companyData['id'],
                'name'           => 'COST ITEM EXPENSE CASH TRANSFER',
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_EXPENSE_CASH_TRANSFER,
                'is_deletable'   => false
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%company_cost_items}}', [
            'type'           => CompanyCostItem::TYPE_EXPENSE,
            'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_EXPENSE_CASH_TRANSFER,
        ]);

        $this->delete('{{%company_cost_items}}', [
            'type'           => CompanyCostItem::TYPE_INCOME,
            'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_INCOME_CASH_TRANSFER,
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180411_110509_add_cash_transfer_cost_item cannot be reverted.\n";

        return false;
    }
    */
}
