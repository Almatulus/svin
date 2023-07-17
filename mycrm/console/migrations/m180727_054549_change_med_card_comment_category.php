<?php

use yii\db\Migration;

/**
 * Class m180727_054549_change_med_card_comment_category
 */
class m180727_054549_change_med_card_comment_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('{{%med_card_comment_categories}}', [
            'service_category_id' => \core\models\ServiceCategory::ROOT_CLINIC
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('{{%med_card_comment_categories}}', [
            'service_category_id' => \core\models\ServiceCategory::ROOT_STOMATOLOGY
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180727_054549_change_med_card_comment_category cannot be reverted.\n";

        return false;
    }
    */
}
