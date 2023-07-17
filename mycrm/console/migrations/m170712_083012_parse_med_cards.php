<?php

use core\models\medCard\MedCard;
use core\models\order\Order;
use yii\db\Migration;

class m170712_083012_parse_med_cards extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%order_tooth}}', 'med_card_id', $this->integer()->unsigned());
        $this->addColumn('{{%order_comments}}', 'med_card_id', $this->integer()->unsigned());

        $this->dropForeignKey('fk_teeth_order', '{{%order_tooth}}');
        $this->dropForeignKey('fk_order_comments_order', '{{%order_comments}}');

        $this->execute('ALTER TABLE {{%order_tooth}} ALTER COLUMN order_id DROP NOT NULL');
        $this->execute('ALTER TABLE {{%order_comments}} ALTER COLUMN order_id DROP NOT NULL');

        $this->addForeignKey('fk_tooth_med_card', '{{%order_tooth}}', 'med_card_id', '{{%med_cards}}', 'id');
        $this->addForeignKey('fk_comment_med_card', '{{%order_comments}}', 'med_card_id', '{{%med_cards}}', 'id');

        $orders = Order::find();

        foreach ($orders->each(30) as $key => $order) {
            if ($order->getOrderComments()->exists() || $order->getOrderTeeth()->exists()) {
                $newMedCard = new MedCard([
                    'order_id' => $order->id
                ]);
                if ($newMedCard->save()) {
                    $this->update('{{%order_tooth}}', [
                        'med_card_id' => $newMedCard->id
                    ], ['order_id' => $order->id]);
                    $this->update('{{%order_comments}}', [
                        'med_card_id' => $newMedCard->id
                    ], ['order_id' => $order->id]);
                }
            }
        }

    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_tooth_med_card', '{{%order_tooth}}');
        $this->dropForeignKey('fk_comment_med_card', '{{%order_comments}}');

        $this->addForeignKey('fk_teeth_order', '{{%order_tooth}}', 'order_id', '{{%orders}}', 'id');
        $this->addForeignKey(
            'fk_order_comments_order',
            '{{%order_comments}}',
            'order_id',
            '{{%orders}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->dropColumn('{{%order_tooth}}', 'med_card_id');
        $this->dropColumn('{{%order_comments}}', 'med_card_id');
        $this->truncateTable('{{%med_cards}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
