<?php

use yii\db\Migration;

/**
 * Handles adding is_trial to table `crm_division_services`.
 */
class m171112_141252_add_is_trial_column_to_crm_division_services_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('crm_division_services', 'is_trial', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('crm_division_services', 'is_trial');
    }
}
