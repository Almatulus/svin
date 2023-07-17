<?php

use yii\db\Migration;

/**
 * Class m180111_180443_add_company_customer_timestamp_attributes
 */
class m180111_180443_add_company_customer_timestamp_attributes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%company_customers}}',
            'updated_time',
            $this->timestamp()
        );
        $this->addColumn(
            '{{%company_customers}}',
            'created_user_id',
            $this->integer()
        );
        $this->addColumn(
            '{{%company_customers}}',
            'updated_user_id',
            $this->integer()
        );

        $this->addForeignKey(
            'fk_company_customers_created_user',
            '{{%company_customers}}',
            'created_user_id',
            '{{%users}}',
            'id'
        );
        $this->addForeignKey(
            'fk_company_customers_updated_user',
            '{{%company_customers}}',
            'updated_user_id',
            '{{%users}}',
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(
            '{{%company_customers}}',
            'updated_time'
        );
        $this->dropColumn(
            '{{%company_customers}}',
            'created_user_id'
        );
        $this->dropColumn(
            '{{%company_customers}}',
            'updated_user_id'
        );
    }
}
