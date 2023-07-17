<?php

use yii\db\Migration;

/**
 * Handles the creation of table `crm_division_service_insurance_companies`.
 */
class m180320_091330_create_crm_division_service_insurance_companies_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('crm_division_service_insurance_companies', [
            'id' => $this->primaryKey(),
            'division_service_id' => $this->integer()->notNull(),
            'insurance_company_id' => $this->integer()->notNull(),
            'price' => $this->integer()->notNull(),
            'price_max' => $this->integer()
        ]);

        $this->addForeignKey('fk_insurance_company_service', '{{%division_service_insurance_companies}}', 'division_service_id', '{{%division_services}}', 'id');
        $this->addForeignKey('fk_insurance_company', '{{%division_service_insurance_companies}}', 'insurance_company_id', '{{%insurance_companies}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_insurance_company_service', '{{%division_service_insurance_companies}}');
        $this->dropForeignKey('fk_insurance_company', '{{%division_service_insurance_companies}}');
        $this->dropTable('crm_division_service_insurance_companies');
    }
}
