<?php

use core\helpers\company\PaymentHelper;
use yii\db\Migration;

/**
 * Class m180111_061411_cashback_init
 */
class m180111_061411_cashback_init extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%payments}}', 'type', $this->integer());

        $this->addColumn('{{%company_customers}}', 'cashback_balance',
            $this->double()->unsigned()->defaultValue(0)->notNull());
        $this->addColumn('{{%company_customers}}', 'cashback_percent',
            $this->integer()->unsigned()->defaultValue(0)->notNull());

        $this->createTable('{{%company_cashbacks}}', [
            'id'                  => $this->primaryKey(),
            'company_customer_id' => $this->integer()->unsigned()->notNull(),
            'type'                => $this->integer(),
            'amount'              => $this->double()->unsigned()->notNull(),
            'percent'             => $this->integer()->unsigned()->notNull(),
            'status'              => $this->integer()->unsigned()->defaultValue(1),
            'created_by'          => $this->integer()->unsigned()->notNull(),
            'updated_by'          => $this->integer()->unsigned()->notNull(),
            'created_at'          => $this->dateTime()->notNull(),
            'updated_at'          => $this->dateTime()->notNull()
        ]);

        $this->addForeignKey('fk_company_cahsback_company_customer', '{{%company_cashbacks}}', 'company_customer_id',
            '{{%company_customers}}', 'id');
        $this->addForeignKey('fk_company_cashback_created_user', '{{%company_cashbacks}}', 'created_by',
            '{{%users}}', 'id');
        $this->addForeignKey('fk_company_cashback_updated_user', '{{%company_cashbacks}}', 'updated_by',
            '{{%users}}', 'id');

        $this->insert('{{%payments}}', [
                'name' => PaymentHelper::get(PaymentHelper::CASHBACK),
                'type' => PaymentHelper::CASHBACK
            ]
        );

        $this->createTable('{{%order_cashbacks}}', [
            'order_id'            => $this->integer()->unsigned()->notNull(),
            'company_cashback_id' => $this->integer()->unsigned()->notNull()
        ]);
        $this->addForeignKey(
            'fk_order',
            '{{%order_cashbacks}}',
            'order_id',
            '{{%orders}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_company_cashback',
            '{{%order_cashbacks}}',
            'company_cashback_id',
            '{{%company_cashbacks}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%order_cashbacks}}');

        $this->dropTable('{{%company_cashbacks}}');
        $this->dropColumn('{{%company_customers}}', 'cashback_balance');
        $this->dropColumn('{{%company_customers}}', 'cashback_percent');

        $cashbackPaymentIds = (new \yii\db\Query())
            ->from('{{%payments}}')
            ->where(['type' => PaymentHelper::CASHBACK])
            ->select('id')
            ->column();

        $this->delete('{{%order_payments}}', ['payment_id' => $cashbackPaymentIds]);
        $this->delete('{{%division_payments}}', ['payment_id' => $cashbackPaymentIds]);
        $this->delete('{{%payments}}', ['type' => PaymentHelper::CASHBACK]);

        $this->dropColumn('{{%payments}}', 'type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180111_061411_cashback_init cannot be reverted.\n";

        return false;
    }
    */
}
