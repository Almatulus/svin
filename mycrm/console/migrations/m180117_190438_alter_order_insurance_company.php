<?php

use core\models\company\Insurance;
use core\models\order\Order;
use yii\db\Migration;

/**
 * Class m180117_190438_alter_order_insurance_company
 */
class m180117_190438_alter_order_insurance_company extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $test_insrances = [1, 4, 18];
        Order::updateAll(
            ['insurance_id' => null],
            ['insurance_id' => $test_insrances]
        );
        Insurance::deleteAll(['id' => $test_insrances]);

        $empty_insurance = Insurance::find()
            ->where(['insurance_company_id' => null])
            ->one();
        if ($empty_insurance) {
            throw new DomainException('Empty insurance exists: ' . $empty_insurance->name);
        }

        $this->addColumn('{{%orders}}', 'insurance_company_id', $this->integer()->unsigned());

        /* @var Insurance[] $insurances */
        $insurances = Insurance::find()->all();
        foreach ($insurances as $insurance) {
            Order::updateAll(
                ['insurance_company_id' => $insurance->insurance_company_id],
                ['insurance_id' => $insurance->id]
            );
        }

        $this->addForeignKey(
            'fk_orders_insurance_company',
            '{{%orders}}',
            'insurance_company_id',
            '{{%insurance_companies}}',
            'id'
        );

        $this->dropColumn('{{%orders}}', 'insurance_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%orders}}', 'insurance_id', $this->integer()->unsigned());

        /* @var Insurance[] $insurances */
        $insurances = Insurance::find()->all();
        foreach ($insurances as $insurance) {
            Order::updateAll(
                ['insurance_id' => $insurance->id],
                ['insurance_company_id' => $insurance->insurance_company_id]
            );
        }

        $this->addForeignKey(
            'fk_orders_insurance',
            '{{%orders}}',
            'insurance_id',
            '{{%company_insurances}}',
            'id'
        );

        $this->dropColumn('{{%orders}}', 'insurance_company_id');
    }
}
