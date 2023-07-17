<?php

use yii\db\Migration;

class m171030_071636_set_company_comment_category extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%med_card_company_comments}}',
            'category_id',
            $this->integer()->unsigned()
        );

        $this->addForeignKey(
            'fk_med_card_company_comments_category',
            '{{%med_card_company_comments}}',
            'category_id',
            '{{%med_card_comment_categories}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $sql = <<<SQL
UPDATE {{%med_card_company_comments}}
SET category_id = {{%med_card_tab_comments}}.category_id
FROM {{%med_card_tab_comments}}

INNER JOIN {{%med_card_tabs}} ON {{%med_card_tabs}}.id = {{%med_card_tab_comments}}.med_card_tab_id
INNER JOIN {{%med_cards}} ON {{%med_cards}}.id = {{%med_card_tabs}}.med_card_id
INNER JOIN {{%orders}} ON {{%orders}}.id = {{%med_cards}}.order_id
INNER JOIN {{%company_customers}} ON {{%company_customers}}.id = {{%orders}}.company_customer_id

WHERE {{%med_card_tab_comments}}.comment LIKE '%' || {{%med_card_company_comments}}.comment || '%' AND 
      {{%med_card_company_comments}}.company_id = {{%company_customers}}.company_id
SQL;
        $this->execute($sql);

        $this->delete('{{%med_card_company_comments}}', ['category_id' => null]);

        $sql = <<<SQL
ALTER TABLE {{%med_card_company_comments}} ALTER COLUMN category_id SET NOT NULL;
SQL;
        $this->execute($sql);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%med_card_company_comments}}', 'category_id');
    }
}
