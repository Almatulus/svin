<?php

use yii\db\Migration;

/**
 * Handles adding created_at to table `crm_company_cashflows`.
 */
class m171117_075149_add_created_at_column_to_crm_company_cashflows_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('crm_company_cashflows', 'created_at', $this->integer()->notNull()->defaultValue(time()));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('crm_company_cashflows', 'created_at');
    }
}
