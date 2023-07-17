<?php

use yii\db\Migration;

/**
 * Handles adding is_deleted to table `crm_company_cashflows`.
 */
class m171117_093307_add_is_deleted_column_to_crm_company_cashflows_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('crm_company_cashflows', 'is_deleted', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('crm_company_cashflows', 'is_deleted');
    }
}
