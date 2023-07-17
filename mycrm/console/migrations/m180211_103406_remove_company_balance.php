<?php

use core\models\company\Company;
use core\models\CompanyPaymentLog;
use yii\db\Migration;

/**
 * Class m180211_103406_remove_company_balance
 */
class m180211_103406_remove_company_balance extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        CompanyPaymentLog::updateAll(
            ['confirmed_time' => date('Y-m-d H:i:s')],
            'confirmed_time IS NULL AND value <= 0'
        );

        foreach (Company::find()->each() as $company) {
            $payment_balance = intval(CompanyPaymentLog::find()
                ->where(['company_id' => $company->id])
                ->andWhere('{{%company_payment_log}}.confirmed_time IS NOT NULL')
                ->sum('value'));
            $company_balance = intval($company->balance);

            if ($company_balance !== $payment_balance) {
                $payment = CompanyPaymentLog::add(
                    $company->id,
                    CompanyPaymentLog::CURRENCY_KZT,
                    'Точка отсчета',
                    '',
                    $company_balance - $payment_balance,
                    true,
                    null
                );
                $payment->created_time = date('Y-m-d H:i:s', 0);

                echo $company->name.
                    " payment_balance:'".$payment_balance.
                    "' company_balance:'".$company->balance.
                    "' payment_value:'".$payment->value."'\n";

                $payment->save();
            }
        }

        $this->dropColumn('{{%companies}}', 'balance');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('{{%companies}}', 'balance', $this->integer()->notNull()->defaultValue(0));

        foreach (Company::find()->each() as $company) {
            $company->balance = $company->getBalance();
            $company->save();
        }
    }
}
