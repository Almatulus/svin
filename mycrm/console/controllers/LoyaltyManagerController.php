<?php

namespace console\controllers;

use core\models\company\Company;
use core\models\customer\CompanyCustomer;
use core\models\customer\CustomerLoyalty;
use core\models\customer\query\CustomerLoyaltyQuery;
use core\models\order\query\OrderQuery;
use yii\console\Controller;

class LoyaltyManagerController extends Controller
{
    /**
     * Runs loyalty manager
     */
    public function actionRun()
    {
        $companies = Company::find()
            ->innerJoinWith([
                'loyaltyPrograms' => function (CustomerLoyaltyQuery $query) {
                    return $query->byEvent(CustomerLoyalty::EVENT_DAY);
                }
            ])
            ->enabled()
            ->andWhere(['not in', '{{%companies}}.id', [181, 56]]);

        foreach ($companies->each(100) as $company) {
            /** @var Company $company */

            // get customers with finished orders
            $companyCustomers = CompanyCustomer::find()->company($company->id)
                ->innerJoinWith([
                    'orders' => function (OrderQuery $query) {
                        $query->finished();
                    }
                ], false);

            $loyalties = $company->loyaltyPrograms;

            foreach ($companyCustomers->each(100) as $companyCustomer) {
                foreach ($loyalties as $loyalty) {
                    $loyalty->process($companyCustomer);
                }
                $companyCustomer->save(false);
            }
        }
    }
}