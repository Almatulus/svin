<?php

use yii\db\Migration;

/**
 * Class m171127_094041_add_division_logo
 */
class m171127_094041_add_division_logo extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%divisions}}',
            'logo_id',
            $this->integer()->unsigned()
        );

        $this->addForeignKey(
            'fk_division_logo',
            '{{%divisions}}',
            'logo_id',
            '{{%images}}',
            'id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%divisions}}', 'logo_id');
    }
}
