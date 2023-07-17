<?php

use core\models\medCard\MedCardTab;
use yii\db\Migration;
use yii\db\Query;

/**
 * Handles the creation of table `{{%med_card_tabs}}`.
 */
class m170713_095450_create_med_card_tabs_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%med_card_tabs}}', [
            'id' => $this->primaryKey(),
            'med_card_id' => $this->integer()->unsigned()
        ]);

        $this->addForeignKey('fk_med_card_tab_med_card', '{{%med_card_tabs}}', 'med_card_id', '{{%med_cards}}', 'id');

        $this->addColumn('{{%order_tooth}}', 'med_card_tab_id', $this->integer()->unsigned());
        $this->addColumn('{{%order_comments}}', 'med_card_tab_id', $this->integer()->unsigned());

        $this->dropForeignKey('fk_tooth_med_card', '{{%order_tooth}}');
        $this->dropForeignKey('fk_comment_med_card', '{{%order_comments}}');

        $this->execute('ALTER TABLE {{%order_tooth}} ALTER COLUMN med_card_id DROP NOT NULL');
        $this->execute('ALTER TABLE {{%order_comments}} ALTER COLUMN med_card_id DROP NOT NULL');

        $this->addForeignKey('fk_tooth_med_card_tab', '{{%order_tooth}}', 'med_card_tab_id', '{{%med_card_tabs}}', 'id');
        $this->addForeignKey('fk_comment_med_card_tab', '{{%order_comments}}', 'med_card_tab_id', '{{%med_card_tabs}}', 'id');

        $query = (new Query())
                ->from('{{%med_cards}}');

        foreach ($query->each(30) as $medCard) {
            $newMedCardTab = new MedCardTab([
                'med_card_id' => $medCard['id']
            ]);
            if ($newMedCardTab->save()) {
                $this->update('{{%order_tooth}}', [
                    'med_card_tab_id' => $newMedCardTab->id
                ], ['med_card_id' => $medCard['id']]);
                $this->update('{{%order_comments}}', [
                    'med_card_tab_id' => $newMedCardTab->id
                ], ['med_card_id' => $medCard['id']]);
            }
        }

        $this->dropColumn('{{%order_tooth}}', 'med_card_id');
        $this->dropColumn('{{%order_comments}}', 'med_card_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%order_tooth}}', 'med_card_id', $this->integer()->unsigned());
        $this->addColumn('{{%order_comments}}', 'med_card_id', $this->integer()->unsigned());

        $this->dropForeignKey('fk_tooth_med_card_tab', '{{%order_tooth}}');
        $this->dropForeignKey('fk_comment_med_card_tab', '{{%order_comments}}');

        $this->addForeignKey('fk_tooth_med_card', '{{%order_tooth}}', 'med_card_id', '{{%med_cards}}', 'id');
        $this->addForeignKey('fk_comment_med_card', '{{%order_comments}}', 'med_card_id', '{{%med_cards}}', 'id');

        $this->dropColumn('{{%order_tooth}}', 'med_card_tab_id');
        $this->dropColumn('{{%order_comments}}', 'med_card_tab_id');

        $this->dropTable('{{%med_card_tabs}}');
    }
}
