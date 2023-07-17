<?php

use core\helpers\company\PaymentHelper;
use core\models\company\Company;
use core\models\finance\CompanyCostItem;
use yii\db\Migration;

/**
 * Class m180405_094705_add_deposit_to_payments
 */
class m180405_094705_add_deposit_to_payments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%payments}}', [
                'name' => 'Deposit',
                'type' => PaymentHelper::DEPOSIT
            ]
        );

//        $payment_id = $this->db->lastInsertID;
//
//        $divisions = (new \yii\db\Query())->from('{{%divisions}}')->select('id')->column();
//
//        foreach ($divisions as $division_id)
//        {
//            $this->insert('{{%division_payments}}', [
//                'division_id' => $division_id,
//                'payment_id' => $payment_id
//            ]);
//        }

        $companies = Company::find()->select(['id'])->asArray();

        foreach ($companies->each(100) as $companyData) {
            $this->insert('{{%company_cost_items}}', [
                'company_id'     => $companyData['id'],
                'name'           => 'COST ITEM INCOME DEPOSIT',
                'type'           => CompanyCostItem::TYPE_INCOME,
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_INCOME,
                'is_deletable'   => false
            ]);

            $this->insert('{{%company_cost_items}}', [
                'company_id'     => $companyData['id'],
                'name'           => 'COST ITEM EXPENSE DEPOSIT',
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_EXPENSE,
                'is_deletable'   => false
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        $payment_id = (new \yii\db\Query())->from('{{%payments}}')->select('id')->where(['type' => PaymentHelper::DEPOSIT])->scalar();

        $this->delete('{{%company_cost_items}}', [
            'type'           => CompanyCostItem::TYPE_EXPENSE,
            'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_EXPENSE,
        ]);

        $this->delete('{{%company_cost_items}}', [
            'type'           => CompanyCostItem::TYPE_INCOME,
            'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_INCOME,
        ]);

//        $this->delete('{{%division_payments}}', ['payment_id' => $payment_id]);
        $this->delete('{{%payments}}', ['type' => PaymentHelper::DEPOSIT]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180403_081426_add_deposit_to_payments cannot be reverted.\n";

        return false;
    }
    */
}
