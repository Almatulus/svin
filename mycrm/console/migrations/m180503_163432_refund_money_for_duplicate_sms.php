<?php

use core\helpers\CompanyHelper;
use core\models\company\Company;
use core\models\CompanyPaymentLog;
use yii\db\Migration;

/**
 * Class m180503_163432_refund_money_for_duplicate_sms
 */
class m180503_163432_refund_money_for_duplicate_sms extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $duplicates = (new \yii\db\Query())
            ->select(['code', 'customer_id', 'company_id', 'COUNT(*) AS c'])
            ->from('crm_customer_requests')
            ->where(['type' => 2])
            ->andWhere(['>=', 'created_time', '2017-09-01'])
            ->groupBy(['code', 'customer_id', 'company_id'])
            ->having('COUNT(*) > 1')
            ->orderBy('company_id DESC')
            ->all();

        $companyRefund = [];

        foreach($duplicates as $duplicate){
            // number of sms in text * sms count * sms price.
            // sms count is decreased by 1 because one sms had to be sent
            $refundTotal = CompanyHelper::estimateNumberOfSms($duplicate['code']) * ($duplicate['c'] - 1) * Yii::$app->params['sms_cost'];

            $payment = CompanyPaymentLog::add(
                $duplicate['company_id'],
                CompanyPaymentLog::CURRENCY_KZT,
                'Восстановление баланса за смс «' . $duplicate['code'] . '»',
                'Восстановление баланса (за дублированные смс)',
                $refundTotal,
                true,
                null
            );
            $payment->save();

            $companyRefund[$duplicate['company_id']]
                = isset($companyRefund[$duplicate['company_id']])
                ? $companyRefund[$duplicate['company_id']] + $refundTotal
                : $refundTotal;
        }

        foreach ($companyRefund as $company_id => $refundTotal) {
            $company = Company::findOne($company_id);
            echo "{$company->name} ({$company_id}): {$refundTotal} тг.\n";
        }

        echo "Total: " . array_sum($companyRefund) . "\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        CompanyPaymentLog::deleteAll(['message' => 'Восстановление баланса (за дублированные смс)']);
    }
}
