<?php

use core\models\customer\CompanyCustomer;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%company_customer_sources}}`.
 */
class m170131_085029_create_company_customer_sources_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%company_customer_sources}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'type' => $this->smallInteger()->notNull()->defaultValue(2),
            'company_id' => $this->integer()->unsigned()
        ]);

        $this->addForeignKey('fk_customer_source_company', '{{%company_customer_sources}}', 'company_id', '{{%companies}}', 'id');

        $this->createIndex('company_customer_sources_company_id_idx', '{{%company_customer_sources}}', 'company_id');
        $this->createIndex('company_customer_sources_type_idx', '{{%company_customer_sources}}', 'type');

        $defaultSources = [
            1 => 'Интернет',
            2 => 'Реклама',
            3 => 'Знакомые',
            4 => 'Социальные сети',
        ];

        foreach ($defaultSources as $key => $sourceName) {
            $this->insert('{{%company_customer_sources}}', [
                'name' => $sourceName,
                'type' => 1
            ]);
        }

        CompanyCustomer::updateAll(['attract' => null], ['attract' => 0]);

        $this->renameColumn('{{%company_customers}}', 'attract', 'source_id');
        $this->alterColumn('{{%company_customers}}', 'source_id', "SET DEFAULT NULL");
        $this->addForeignKey('fk_company_customer_source', '{{%company_customers}}', 'source_id', '{{%company_customer_sources}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_company_customer_source', '{{%company_customers}}', 'source_id', '{{%company_customer_sources}}', 'id');
        $this->alterColumn('{{%company_customers}}', 'source_id', "SET DEFAULT 0");
        $this->renameColumn('{{%company_customers}}', 'source_id', 'attract');

        CompanyCustomer::updateAll(['attract' => 0], 'attract is NULL');

        $this->dropTable('{{%company_customer_sources}}');
    }
}
