<?php

use yii\db\Migration;

/**
 * Class m180109_195151_add_company_customer_insurance_company
 */
class m180109_195151_add_company_customer_insurance_company extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%company_customers}}',
            'insurance_company_id',
            $this->integer()
        );

        $this->addForeignKey(
            'fk_company_customers_insurance_company',
            '{{%company_customers}}',
            'insurance_company_id',
            '{{%insurance_companies}}',
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%company_customers}}', 'insurance_company_id');
    }
}
