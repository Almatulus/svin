<?php

use yii\db\Migration;

/**
 * Handles adding created_by to table `crm_company_cashflows`.
 * Has foreign keys to the tables:
 *
 * - `crm_users`
 */
class m171117_091151_add_created_by_column_to_crm_company_cashflows_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('crm_company_cashflows', 'created_by', $this->integer()->notNull()->defaultValue(8));

        // creates index for column `created_by`
        $this->createIndex(
            'idx-crm_company_cashflows-created_by',
            'crm_company_cashflows',
            'created_by'
        );

        // add foreign key for table `crm_users`
        $this->addForeignKey(
            'fk-crm_company_cashflows-created_by',
            'crm_company_cashflows',
            'created_by',
            'crm_users',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `crm_users`
        $this->dropForeignKey(
            'fk-crm_company_cashflows-created_by',
            'crm_company_cashflows'
        );

        // drops index for column `created_by`
        $this->dropIndex(
            'idx-crm_company_cashflows-created_by',
            'crm_company_cashflows'
        );

        $this->dropColumn('crm_company_cashflows', 'created_by');
    }
}
