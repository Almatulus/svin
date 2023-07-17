<?php

use core\models\division\DivisionService;
use yii\db\Migration;

/**
 * Class m180530_051940_fix_services
 */
class m180530_051940_fix_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(DivisionService::tableName(), ['price_max' => null], "price > price_max");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180530_051940_fix_services cannot be reverted.\n";

        return false;
    }
    */
}
