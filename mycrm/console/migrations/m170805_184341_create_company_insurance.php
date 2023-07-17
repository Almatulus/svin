<?php

use yii\db\Migration;

class m170805_184341_create_company_insurance extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%company_insurances}}', [
            'id'           => $this->primaryKey(),
            'company_id'   => $this->integer()->unsigned()->notNull(),
            'name'         => $this->string()->notNull(),
            'description'  => $this->text(),
            'deleted_time' => $this->dateTime()->defaultValue(null),
        ]);

        $this->addForeignKey(
            'fk_company_insurances_company',
            '{{%company_insurances}}',
            'company_id',
            '{{%companies}}',
            'id'
        );

        $this->addColumn(
            '{{%orders}}',
            'insurance_id',
            $this->integer()->unsigned()->defaultValue(null)
        );

        $this->addForeignKey(
            'fk_orders_insurance',
            '{{%orders}}',
            'insurance_id',
            '{{%company_insurances}}',
            'id'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%orders}}', 'insurance_id');
        $this->dropTable('{{%company_insurances}}');
    }
}
