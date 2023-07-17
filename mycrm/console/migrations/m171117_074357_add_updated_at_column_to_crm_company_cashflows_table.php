<?php

use yii\db\Migration;

/**
 * Handles adding updated_at to table `crm_company_cashflows`.
 */
class m171117_074357_add_updated_at_column_to_crm_company_cashflows_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('crm_company_cashflows', 'updated_at', $this->integer()->notNull()->defaultValue(time()));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('crm_company_cashflows', 'updated_at');
    }
}
