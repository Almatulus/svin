<?php

use yii\db\Migration;

/**
 * Class m180419_125624_add_see_customer_phones_column_to_staffs
 */
class m180419_125624_add_see_customer_phones_column_to_staffs extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%staffs}}', 'see_customer_phones', $this->boolean()->after('create_order'));

        $this->execute("
            UPDATE {{%staffs}}
            SET see_customer_phones=true
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%staffs}}', 'see_customer_phones');
    }
}
