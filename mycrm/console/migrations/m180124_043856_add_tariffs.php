<?php

use yii\db\Migration;

/**
 * Class m180124_043856_add_tariffs
 */
class m180124_043856_add_tariffs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%company_tariffs}}', [
            'id'         => $this->primaryKey(),
            'name'       => $this->string()->notNull(),
            'staff_qty'  => $this->integer()->notNull(),
            'price'      => $this->integer()->notNull(),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(false)
        ]);

        $this->insert('{{%company_tariffs}}', [
            'name'      => 'Basic',
            'staff_qty' => 100,
            'price'     => 0
        ]);

        $this->dropColumn('{{%companies}}', 'tariff');
        $this->addColumn('{{%companies}}', 'tariff_id', $this->integer()->unsigned());
        $this->addForeignKey('fk-tariff', '{{%companies}}', 'tariff_id', '{{%company_tariffs}}', 'id');

        $this->update('{{%companies}}', ['tariff_id' => $this->db->lastInsertID]);
        $this->execute('ALTER TABLE {{%companies}} ALTER COLUMN tariff_id SET NOT NULL');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%companies}}', 'tariff', $this->integer()->unsigned()->notNull()->defaultValue(0));
        $this->dropColumn('{{%companies}}', 'tariff_id');

        $this->dropTable('{{%company_tariffs}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180124_043856_add_tariffs cannot be reverted.\n";

        return false;
    }
    */
}
