<?php

use core\models\Staff;
use yii\db\Migration;

/**
 * Class m180328_085112_add_permissions_to_staff
 */
class m180328_085112_add_permissions_to_staff extends Migration
{
    private $data = [
        // customers
        'companyCustomerOwner' => [
            'companyCustomerCategoryAdmin',
            'companyCustomerLoyaltyAdmin',
            'companyCustomerLostView',
            'companyCustomerSubscriptionAdmin',
            'companySourceAdmin',
        ],
        // orders
        'orderOwner'           => [
            'staffReviewAdmin',
            'divisionReviewAdmin',
            'customerRequestView',
        ],
        // finances
        'cashOwner'            => [
            'companyContractorAdmin',
            'companyCostItemAdmin',
            'schemeAdmin',
            'salaryPay',
            'companyCashflowAdmin',
            'salaryReportView',
            'reportPeriodView',
            'reportStaffView',
            'reportBalanceView',
            'reportReferrerView',
            'cashbackAdmin',
        ],
        // statistics
        'statisticView'        => [
            'statisticStaffView',
            'statisticServiceView',
            'statisticCustomerView',
            'statisticInsuranceView',
        ],
        // settings
        'companyOwner'         => [
            'staffAdmin',
            'scheduleAdmin',
            'companyPositionAdmin',
            'smsTemplatesAdmin',
            'documentTemplateAdmin',
            'paymentAdmin',
            'webcallAdmin',
            'insuranceCompanyAdmin',
            'teethDiagnosisAdmin',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $authManager = \Yii::$app->authManager;

        foreach ($this->data as $parentPermission => $childPermissions) {
            $assignments = $authManager->getUserIdsByRole($parentPermission);

            foreach ($assignments as $user_id) {

                $staff = Staff::find()->enabled()->andWhere(['user_id' => $user_id])->exists();

                if ($staff) {
                    foreach ($childPermissions as $permissionName) {
                        $permission = $authManager->getPermission($permissionName);
                        if ($permission) {
                            echo "GRANT {$permissionName} to $user_id" . PHP_EOL;
                            $authManager->assign($permission, $user_id);
                        }
                    }
                }

            }

        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $authManager = \Yii::$app->authManager;

        foreach ($this->data as $parentPermission => $childPermissions) {
            $assignments = $authManager->getUserIdsByRole($parentPermission);

            foreach ($assignments as $user_id) {

                $staff = Staff::find()->enabled()->andWhere(['user_id' => $user_id])->exists();

                if ($staff) {
                    foreach ($childPermissions as $permissionName) {
                        $permission = $authManager->getPermission($permissionName);
                        if ($permission) {
                            echo "REVOKE {$permissionName} to $user_id" . PHP_EOL;
                            $authManager->revoke($permission, $user_id);
                        }
                    }
                }

            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180328_085112_add_permissions_to_staff cannot be reverted.\n";

        return false;
    }
    */
}
