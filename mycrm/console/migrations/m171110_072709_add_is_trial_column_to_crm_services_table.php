<?php

use yii\db\Migration;

/**
 * Handles adding is_trial to table `crm_services`.
 */
class m171110_072709_add_is_trial_column_to_crm_services_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('crm_services', 'is_trial', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('crm_services', 'is_trial');
    }
}
