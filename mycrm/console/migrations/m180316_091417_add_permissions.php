<?php

use yii\db\Migration;

/**
 * Class m180316_091417_add_permissions
 */
class m180316_091417_add_permissions extends Migration
{
    private $data = [
        'administrator;company' => [
            // customers
            'companyCustomerCategoryAdmin',
            'companyCustomerLoyaltyAdmin',
            'companyCustomerLostView',
            'companyCustomerSubscriptionAdmin',
            'companySourceAdmin',

            // orders
            'staffReviewAdmin',
            'divisionReviewAdmin',
            'customerRequestView',

            // finances
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

            // statistics
            'statisticStaffView',
            'statisticServiceView',
            'statisticCustomerView',
            'statisticInsuranceView',

            // settings
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
        $authManager = Yii::$app->authManager;

        foreach ($this->data as $parentPermissions => $childPermissions) {
            $parentPermissions = explode(";", $parentPermissions);
            foreach ($childPermissions as $childPermissionName) {
                $childPermission = $authManager->createPermission($childPermissionName);
                $authManager->add($childPermission);
                foreach ($parentPermissions as $parentPermissionName) {
                    if ($parentPermission = Yii::$app->authManager->getRole($parentPermissionName)) {
                        $authManager->addChild($parentPermission, $childPermission);
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
        $authManager = Yii::$app->authManager;

        foreach ($this->data as $parentPermissions => $childPermissions) {
            foreach ($childPermissions as $childPermissionName) {
                $childPermission = $authManager->getPermission($childPermissionName);
                $authManager->remove($childPermission);
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
        echo "m180316_091417_add_permissions cannot be reverted.\n";

        return false;
    }
    */
}
