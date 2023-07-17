<?php

use yii\db\Migration;

/**
 * Class m180524_064620_add_has_comments_to_document_form
 */
class m180524_064620_add_has_comments_to_document_form extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%document_form_elements}}', 'is_comment', $this->boolean()->defaultValue(false));

        $elements = (new \yii\db\Query())->from('{{%document_form_elements}}')
            ->select('id, order, CAST(key AS int)')
            ->where(['depends_on' => 'diagnosis_id'])
            ->orderBy('key ASC')
            ->all();

        foreach ($elements as $element) {
            $this->update('{{%document_form_elements}}', [
                'is_comment' => true,
                'key'        => $element['key'] - 1,
                'order'      => $element['order'] + 1
            ], [
                'id' => $element['id']
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%document_form_elements}}', 'is_comment');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180524_064620_add_has_comments_to_document_form cannot be reverted.\n";

        return false;
    }
    */
}
