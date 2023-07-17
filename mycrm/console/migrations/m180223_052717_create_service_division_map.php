<?php

use yii\db\Migration;

/**
 * Class m180223_052717_create_service_division_map
 */
class m180223_052717_create_service_division_map extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%service_division_map}}', [
            'division_service_id' => $this->integer()->unsigned()->notNull(),
            'division_id'         => $this->integer()->unsigned()->notNull()
        ]);

        $this->addPrimaryKey($this->db->tablePrefix . 'service_division_map_pkey', '{{%service_division_map}}', [
            'division_service_id',
            'division_id'
        ]);
        $this->addForeignKey('fk_service_division_map_service', '{{%service_division_map}}', 'division_service_id',
            '{{%division_services}}', 'id');
        $this->addForeignKey('fk_service_division_map_division', '{{%service_division_map}}', 'division_id',
            '{{%divisions}}', 'id');

        $services = (new \yii\db\Query())->from('{{%division_services}}')->select(['id', 'division_id'])->all();
        foreach ($services as $serviceData) {
            $this->insert('{{%service_division_map}}', [
                'division_service_id' => $serviceData['id'],
                'division_id'         => $serviceData['division_id']
            ]);
        }

        $this->dropColumn('{{%division_services}}', 'division_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%division_services}}', 'division_id', $this->integer()->unsigned());
        $this->addForeignKey('fk_division_services_division', '{{%division_services}}', 'division_id', '{{%divisions}}',
            'id');

        $services = (new \yii\db\Query())->from('{{%service_division_map}}')->select([
            'division_id',
            'division_service_id'
        ])->all();
        foreach ($services as $serviceData) {
            $this->update('{{%division_services}}', ['division_id' => $serviceData['division_id']],
                ['id' => $serviceData['division_service_id']]);
        }

        $this->execute('ALTER TABLE {{%division_services}} ALTER COLUMN division_id SET NOT NULL');

        $this->dropTable('{{%service_division_map}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180223_052717_create_service_division_map cannot be reverted.\n";

        return false;
    }
    */
}
