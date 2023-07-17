<?php

use yii\db\Migration;

/**
 * Handles the creation of table `med_cards`.
 */
class m170712_081349_create_med_cards_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%med_cards}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->unsigned()->unique()->notNull(),
            'number' => $this->integer()
        ]);
        $this->addForeignKey('fk_med_card_order', '{{%med_cards}}', 'order_id', '{{%orders}}', 'id');

        $this->createTrigger();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER IF EXISTS increment_med_card_number ON crm_med_cards');
        $this->execute('DROP FUNCTION IF EXISTS increment_med_card_number()');
        $this->dropTable('{{%med_cards}}');
    }

    private function createTrigger()
    {
        $sql = '
            CREATE OR REPLACE FUNCTION increment_med_card_number() RETURNS TRIGGER as $increment_med_card_number$
                DECLARE
                    maxNumber integer;
                    companyCustomerId integer;
                BEGIN
                    SELECT company_customer_id
                        INTO companyCustomerId
                    FROM crm_orders
                    WHERE id = NEW.order_id;

                    SELECT max(crm_med_cards.number)
                        INTO maxNumber
                    FROM crm_med_cards
                    LEFT JOIN crm_orders
                    ON crm_orders.id = crm_med_cards.order_id
                    WHERE crm_orders.company_customer_id = companyCustomerId;

                    IF maxNumber IS NULL THEN
                        NEW.number = 1;
                    ELSE
                        NEW.number = maxNumber + 1;
                    END IF;

                    RETURN NEW;
                END;
            $increment_med_card_number$ LANGUAGE plpgsql;
        ';

        $this->execute($sql);
        $this->execute("CREATE TRIGGER increment_med_card_number BEFORE INSERT ON crm_med_cards
            FOR EACH ROW EXECUTE PROCEDURE increment_med_card_number();");
    }
}
