<?php

use core\models\division\DivisionService;
use yii\db\Migration;

// + crm_delayed_notifications_queue
// + crm_company_cashflow_services
// ! crm_division_services_map
// + crm_document_services
// + crm_division_service_insurance_companies
// + crm_med_card_tab_services
// + crm_payroll_services
// + crm_division_service_products
// + crm_order_services
// + crm_service_division_map
// + crm_staff_division_service_map
// + crm_customer_subscription_services

/**
 * Class m211124_024041_fix_services
 */
class m211124_024041_fix_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /** @property $copyServices DivisionService[] */
        $copyServices = DivisionService::find()
            ->joinWith('divisions')
            ->andWhere(['{{%service_division_map}}.division_id' => 328])
            ->orderBy('id')
            ->all();

        $idx = 1;
        foreach ($copyServices as $service) {
            echo $idx++ . '/' . count($copyServices);
            if (count($service->divisions) > 1) {
                continue;
            }

            $root_service = DivisionService::find()
                ->joinWith('divisions')
                ->andWhere(['service_name' => $service->service_name])
                ->andWhere(['average_time' => $service->average_time])
                ->andWhere(['price' => $service->price])
                ->andWhere(['<>', '{{%division_services}}.id', $service->id])
                ->andWhere(['{{%service_division_map}}.division_id' => 205])
                ->one();

            if ($root_service !== null) {
                $this->moveService($root_service, $service);
                $this->update(
                    DivisionService::tableName(),
                    ['status' => DivisionService::STATUS_DELETED],
                    'id = :id',
                    [':id' => $service->id]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211124_024041_fix_services cannot be reverted.\n";
    }

    private function moveService($rootService, $copyService)
    {
        \core\models\customer\DelayedNotification::updateAll(
            ['division_service_id' => $rootService->id],
            ['division_service_id' => $copyService->id]
        );
        \core\models\finance\CompanyCashflowService::updateAll(
            ['service_id' => $rootService->id],
            ['service_id' => $copyService->id]
        );
        \core\models\document\DocumentService::updateAll(
            ['service_id' => $rootService->id],
            ['service_id' => $copyService->id]
        );
        \core\models\division\DivisionServiceInsuranceCompany::updateAll(
            ['division_service_id' => $rootService->id],
            ['division_service_id' => $copyService->id]
        );
        \core\models\medCard\MedCardTabService::updateAll(
            ['division_service_id' => $rootService->id],
            ['division_service_id' => $copyService->id]
        );
        \core\models\finance\PayrollService::updateAll(
            ['division_service_id' => $rootService->id],
            ['division_service_id' => $copyService->id]
        );
//        \core\models\division\DivisionServiceProduct::updateAll(
//            ['division_service_id' => $rootService->id],
//            ['division_service_id' => $copyService->id]
//        );
        \core\models\order\OrderService::updateAll(
            ['division_service_id' => $rootService->id],
            ['division_service_id' => $copyService->id]
        );
        \core\models\customer\CustomerSubscriptionService::updateAll(
            ['division_service_id' => $rootService->id],
            ['division_service_id' => $copyService->id]
        );

        $sql = ' SELECT * FROM {{%service_division_map}} ' .
            ' WHERE division_service_id = ' . $rootService->id .
            ' AND division_id = 328';
        $exist = count(Yii::$app->db->createCommand($sql)->queryAll()) > 0;
        if (!$exist) {
            $this->insert(
                '{{%service_division_map}}',
                [
                    'division_service_id' => $rootService->id,
                    'division_id' => 328
                ]
            );
        }

        foreach ($copyService->staffs as $staff) {
            $sql = ' SELECT * FROM {{%staff_division_service_map}} ' .
                ' WHERE division_service_id = ' . $rootService->id .
                ' AND staff_id = ' . $staff->id;
            $exist = count(Yii::$app->db->createCommand($sql)->queryAll()) > 0;
            if (!$exist) {
                $this->insert(
                    '{{%staff_division_service_map}}',
                    [
                        'division_service_id' => $rootService->id,
                        'staff_id' => $staff->id
                    ]
                );
            }
        }
    }
}
