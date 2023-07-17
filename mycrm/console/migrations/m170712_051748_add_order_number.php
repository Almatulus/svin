<?php

use core\models\company\Company;
use core\models\order\Order;
use yii\db\Migration;

class m170712_051748_add_order_number extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%orders}}', 'number', $this->integer()->unsigned());

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
        $this->execute("CREATE TRIGGER increment_order_number BEFORE INSERT ON crm_orders
            FOR EACH ROW EXECUTE PROCEDURE increment_order_number();");

        $companies = Company::find()->select('id')->column();

        foreach ($companies as $key => $company_id) {
            $counter = 1;
            $orderQuery = Order::find()->joinWith('staff.division', false)
                ->andWhere(['company_id' => $company_id])
                ->orderBy('datetime ASC');

            foreach ($orderQuery->each(30) as $key => $order) {
                $order->number = $counter;
                $counter++;
                $order->update();
            }
        }
    }

    public function safeDown()
    {
        $this->execute('DROP TRIGGER IF EXISTS increment_order_number ON crm_orders');
        $this->execute('DROP FUNCTION IF EXISTS increment_order_number()');
        $this->dropColumn('{{%orders}}', 'number');
    }
}
