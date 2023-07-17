<?php

use yii\db\Migration;

/**
 * Class m180205_063424_add_company_payment
 */
class m180205_063424_add_company_payment extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%company_tariff_payments}}', [
            'id'         => $this->primaryKey(),
            'sum'        => $this->integer()->unsigned()->notNull(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'period'     => $this->integer()->notNull(),
            'start_date' => $this->date()->notNull(),
            'created_at' => $this->dateTime()->notNull()
        ]);

        $this->dropColumn('{{%companies}}', 'last_payment');

        $this->addForeignKey('fk_company_tariff_payments_company', '{{%company_tariff_payments}}',
            'company_id', '{{%companies}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%company_tariff_payments}}');

        $this->addColumn('{{%companies}}', 'last_payment', $this->date());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180205_063424_add_company_payment cannot be reverted.\n";

        return false;
    }
    */
}
