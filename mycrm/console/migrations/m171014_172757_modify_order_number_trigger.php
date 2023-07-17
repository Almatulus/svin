<?php

use yii\db\Migration;

class m171014_172757_modify_order_number_trigger extends Migration
{
    public function safeUp()
    {
        $sql = '
            CREATE OR REPLACE FUNCTION increment_order_number() RETURNS TRIGGER as $increment_order_number$
                DECLARE
                    maxNumber integer;
                BEGIN
                    SELECT max(number)
                        INTO maxNumber
                    FROM crm_orders
                    WHERE NEW.division_id = crm_orders.division_id;

                    IF maxNumber IS NULL THEN
                        NEW.number = 1;
                    ELSE
                        NEW.number = maxNumber + 1;
                    END IF;

                    RETURN NEW;
                END;
            $increment_order_number$ LANGUAGE plpgsql;
        ';

        $this->execute($sql);
    }

    public function safeDown()
    {
        $sql = '
            CREATE OR REPLACE FUNCTION increment_order_number() RETURNS TRIGGER as $increment_order_number$
                DECLARE
                    maxNumber integer;
                    divisionId integer;
                BEGIN
                    SELECT division_id
                        INTO divisionId
                    FROM crm_staffs
                    WHERE id = NEW.staff_id;

                    SELECT max(number)
                        INTO maxNumber
                    FROM crm_orders
                    LEFT JOIN crm_staffs
                    ON crm_staffs.id = crm_orders.staff_id
                    WHERE crm_staffs.division_id = divisionId;

                    IF maxNumber IS NULL THEN
                        NEW.number = 1;
                    ELSE
                        NEW.number = maxNumber + 1;
                    END IF;

                    RETURN NEW;
                END;
            $increment_order_number$ LANGUAGE plpgsql;
        ';

        $this->execute($sql);
    }
}
