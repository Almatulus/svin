<?php

use yii\db\Migration;

/**
 * Handles the creation for table `crm_company_notifications_info`.
 */
class m160915_093517_create_crm_company_notices_info_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('crm_company_notices_info', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'email_count' => $this->integer()->defaultValue(0),
            'email_limit' => $this->integer()->defaultValue(0),
            'push_count' => $this->integer()->defaultValue(0),
            'push_limit' => $this->integer()->defaultValue(0),
            'sms_count' => $this->integer()->defaultValue(0),
            'sms_limit' => $this->integer()->defaultValue(0),
        ]);
        $this->addForeignKey('fk_company','crm_company_notices_info','company_id','crm_companies','id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_company','crm_company_notices_info');
        $this->dropTable('crm_company_notices_info');
    }
}
