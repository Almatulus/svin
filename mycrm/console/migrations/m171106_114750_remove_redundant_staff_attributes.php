<?php

use yii\db\Migration;

/**
 * Class m171106_114750_remove_redundant_staff_attributes
 */
class m171106_114750_remove_redundant_staff_attributes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('{{%staffs}}', 'user_email');
        $this->dropColumn('{{%staffs}}', 'user_confirm');

        $sql = <<<SQL
ALTER TABLE {{%staffs}} ALTER COLUMN image_id DROP NOT NULL;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%staffs}} ALTER COLUMN surname DROP NOT NULL;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%staffs}} ALTER COLUMN birth_date DROP NOT NULL;
SQL;
        $this->execute($sql);

        $this->addColumn(
            '{{%staffs}}',
            'created_at',
            $this->timestamp()->notNull()->defaultExpression('NOW()')
        );
        $this->addColumn(
            '{{%staffs}}',
            'updated_at',
            $this->timestamp()->notNull()->defaultExpression('NOW()')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%staffs}}', 'created_at');
        $this->dropColumn('{{%staffs}}', 'updated_at');

        $sql = <<<SQL
ALTER TABLE {{%staffs}} ALTER COLUMN image_id SET NOT NULL;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
ALTER TABLE {{%staffs}} ALTER COLUMN surname SET NOT NULL;
SQL;
        $this->execute($sql);
        
        $this->addColumn('{{%staffs}}', 'user_email', $this->string());
        $this->addColumn('{{%staffs}}', 'user_confirm', $this->string());
    }
}
