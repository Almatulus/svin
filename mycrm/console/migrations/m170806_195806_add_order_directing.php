<?php

use yii\db\Migration;

class m170806_195806_add_order_directing extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%company_referrers}}', [
            'id'         => $this->primaryKey(),
            'name'       => $this->string()->notNull(),
            'company_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->createIndex(
            'uq_company_referrers_name_company',
            '{{%company_referrers}}',
            ['name', 'company_id'],
            true
        );

        $this->addForeignKey(
            'fk_company_referrers_company',
            '{{%company_referrers}}',
            'company_id',
            '{{%companies}}',
            'id'
        );

        $this->addColumn(
            '{{%orders}}',
            'referrer_id',
            $this->integer()->unsigned()->defaultValue(null)
        );

        $this->addForeignKey(
            'fk_orders_referrer',
            '{{%orders}}',
            'referrer_id',
            '{{%company_referrers}}',
            'id'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%orders}}', 'referrer_id');
        $this->dropTable('{{%company_referrers}}');
    }
}
