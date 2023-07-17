<?php

use core\models\medCard\MedCardComment;
use core\models\medCard\MedCardCompanyComment;
use yii\db\Migration;

/**
 * Class m180215_050746_remove_redundant_comments
 */
class m180215_050746_remove_redundant_comments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->trimComments();

        $this->removeRedundant();
        $this->removeRedundant();
        $this->removeRedundant();
        $this->removeDuplicate();

        $this->createIndex('uq_med_card_company_comments', '{{%med_card_company_comments}}', ['company_id', 'category_id', 'comment'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('uq_med_card_company_comments', '{{%med_card_company_comments}}');
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function trimComments()
    {
        foreach (MedCardComment::find()->each() as $comment) {
            /* @var MedCardComment $comment */
            $comment->comment = trim($comment->comment);
            $comment->update(false);
        }
    }

    private function removeDuplicate()
    {
        $sql = <<<SQL
DELETE
FROM
    crm_med_card_company_comments a USING crm_med_card_company_comments b
WHERE
    a.id < b.id
    AND a.company_id = b.company_id
    AND a.comment = b.comment
    AND a.category_id = b.category_id;
SQL;

        $this->execute($sql);

        foreach (MedCardComment::find()->each() as $comment) {
            /* @var MedCardComment $comment */
            MedCardCompanyComment::deleteAll([
                'category_id' => $comment->category_id,
                'comment' => $comment->comment
            ]);
        }
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function removeRedundant()
    {
        foreach (MedCardCompanyComment::find()->each() as $companyComment) {
            /* @var MedCardCompanyComment $companyComment */
            $companyComment->comment
                = MedCardCompanyComment::clearComment($companyComment->comment);

            if (empty($companyComment->comment)) {
                $companyComment->delete();
            } else {
                $companyComment->update(false);
            }
        }
    }
}
