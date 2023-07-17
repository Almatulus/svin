<?php

use yii\db\Migration;

class m170825_225032_create_company_comment_templates extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%med_card_company_comments}}', [
            'id'         => $this->primaryKey(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'comment'    => $this->text()->notNull()
        ]);

        $this->addForeignKey(
            'fk_med_card_company_comments_company',
            '{{%med_card_company_comments}}',
            'company_id',
            '{{%companies}}',
            'id'
        );

        $this->renameTable('{{%diagnoses}}', '{{%med_card_diagnoses}}');
        $this->renameTable('{{%diagnose_classes}}', '{{%med_card_diagnose_classes}}');
        $this->renameTable('{{%comment_templates}}', '{{%med_card_comments}}');
        $this->renameTable('{{%comment_template_categories}}', '{{%med_card_comment_categories}}');
        $this->renameTable('{{%comment_template_diagnosis_map}}', '{{%med_card_comment_diagnosis_map}}');
    }

    public function safeDown()
    {
        $this->dropTable('{{%med_card_company_comments}}');
        $this->renameTable('{{%med_card_diagnoses}}', '{{%diagnoses}}');
        $this->renameTable('{{%med_card_diagnose_classes}}', '{{%diagnose_classes}}');
        $this->renameTable('{{%med_card_comments}}', '{{%comment_templates}}');
        $this->renameTable('{{%med_card_comment_categories}}', '{{%comment_template_categories}}');
        $this->renameTable('{{%med_card_comment_diagnosis_map}}', '{{%comment_template_diagnosis_map}}');
    }
}
