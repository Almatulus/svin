<?php

use yii\db\Migration;

class m170827_112545_add_company_position_comment_categories extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%company_position_med_cart_comment_category_map}}',
            [
                'company_position_id'          =>
                    $this->integer()->unsigned()->notNull(),
                'med_card_comment_category_id' =>
                    $this->integer()->unsigned()->notNull(),
            ]);

        $this->createIndex(
            'uq_company_position_med_cart_comment_category_map_company_position_med_card_comment_category',
            '{{%company_position_med_cart_comment_category_map}}',
            ['company_position_id', 'med_card_comment_category_id'],
            true
        );

        $this->addForeignKey(
            'fk_uq_company_position_med_cart_comment_category_map_company_position',
            '{{%company_position_med_cart_comment_category_map}}',
            'company_position_id',
            '{{%company_positions}}',
            'id'
        );

        $this->addForeignKey(
            'fk_uq_company_position_med_cart_comment_category_map_med_card_comment_category',
            '{{%company_position_med_cart_comment_category_map}}',
            'med_card_comment_category_id',
            '{{%med_card_comment_categories}}',
            'id'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%company_position_med_cart_comment_category_map}}');
    }
}
