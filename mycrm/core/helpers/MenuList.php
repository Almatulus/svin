<?php

namespace core\helpers;

use Yii;

/**
 * Class MenuList
 * @package core\helpers
 */
class MenuList
{
    const CUSTOMERS = 'customers';
    const FINANCE = 'finance';
    const ORDERS = 'orders';
    const SERVICES = 'services';
    const SETTINGS = 'settings';
    const STATISTIC = 'statistic';
    const TIMETABLE = 'timetable';
    const WAREHOUSE = 'warehouse';

    /**
     * @return array
     */
    public static function all(): array
    {
        return [
            self::CUSTOMERS => ['companyCustomerOwner', 'companyCustomerAdmin'],
            self::FINANCE   => 'cashOwner',
            self::ORDERS    => ['orderOwner', 'orderAdmin'],
            self::SERVICES  => 'divisionServiceView',
            self::SETTINGS  => ['companyOwner', 'companyAdmin'],
            self::STATISTIC => 'statisticView',
            self::TIMETABLE => 'timetableView',
            self::WAREHOUSE => 'warehouseAdmin',
        ];
    }

    /**
     * @return array
     */
    public static function modules(): array
    {
        return [
            self::TIMETABLE,
            self::CUSTOMERS,
            self::ORDERS,
            self::FINANCE,
            self::STATISTIC,
            self::SERVICES,
            self::WAREHOUSE,
            self::SETTINGS,
        ];
    }

    /**
     * @param string $url
     * @param array $params
     * @return array
     */
    public static function moduleItems(string $url, array $params): array
    {
        return [
            self::CUSTOMERS => self::getCustomerItems($url, $params),
            self::FINANCE   => self::getFinanceItems($url),
            self::ORDERS    => [
                [
                    'label'   => Yii::t('app', 'Orders'),
                    'icon'    => 'fa fa-list',
                    'url'     => ['/order/order/index'],
                    'visible' => Yii::$app->user->can('orderView')
                ],
                [
                    'label'   => Yii::t('app', 'Staff Reviews'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/staff-review/index'],
                    'visible' => Yii::$app->user->can('staffReviewAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Division Reviews'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/division/review/index'],
                    'visible' => Yii::$app->user->can('divisionReviewAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'SMS'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/customer/customer-request/index'],
                    'visible' => Yii::$app->user->can('customerRequestView')
                ],
                [
                    'label'   => Yii::t('app', 'Web Calls'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/webcall/default/calls'],
                    'active'  => strpos(Yii::$app->request->url, '/webcall/default/calls') !== false,
                    'visible' => Yii::$app->hasModule('webcall') && Yii::$app->user->identity->company->hasWebCallAccess()
                        && Yii::$app->user->can('webcallAdmin')
                ],
            ],
            self::SERVICES  => (isset($params['sideNavID']) && $params['sideNavID'] == self::SERVICES)
                ? $params['sideNavOptions'] : [],
            self::SETTINGS  => [
                [
                    'label'   => Yii::t('app', 'Company information'),
                    'icon'    => 'fa fa-info',
                    'url'     => ['/company/default/update'],
                    'active'  => strpos(Yii::$app->request->url, 'company/default/update') !== false
                        || strpos(Yii::$app->request->url, '/division/division/') !== false,
                    'visible' => Yii::$app->user->can('companyView')
                ],
                [
                    'label'   => Yii::t('app', 'Staff'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/staff/index'],
                    'active'  => strpos(Yii::$app->request->url, '/staff/') !== false &&
                        strpos(Yii::$app->request->url, '/statistic/staff') === false,
                    'visible' => Yii::$app->user->can('staffAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Time Schedule'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/schedule/index'],
                    'active'  => strpos(Yii::$app->request->url, '/schedule/index') !== false,
                    'visible' => Yii::$app->user->can('scheduleAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Company positions'),
                    'icon'    => 'fa fa-gem',
                    'url'     => ['/company/position/index'],
                    'active'  => strpos(Yii::$app->request->url, '/company/position/') !== false,
                    'visible' => Yii::$app->user->can('companyPositionAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Request Templates'),
                    'url'     => ['/customer/customer-request/settings'],
                    'icon'    => 'fa fa-envelope',
                    'visible' => Yii::$app->user->can('smsTemplatesAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Company Documents'),
                    'url'     => ['/company/document/index'],
                    'icon'    => 'fa fa-envelope',
                    'active'  => strpos(Yii::$app->request->url, '/company/document/') !== false,
                    'visible' => Yii::$app->user->can('documentTemplateAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Pay account'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/company/default/payment'],
                    'visible' => Yii::$app->user->can('paymentAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Web Calls'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/webcall/default/settings'],
                    'active'  => strpos(Yii::$app->request->url, '/webcall/default/settings') !== false
                        || strpos(Yii::$app->request->url, '/webcall/account') !== false,
                    'visible' => Yii::$app->hasModule('webcall') && Yii::$app->user->identity->company->hasWebCallAccess()
                        && Yii::$app->user->can('webcallAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Insurance Companies'),
                    'url'     => ['/company/insurance/index'],
                    'icon'    => 'fa fa-medkit',
                    'visible' => Yii::$app->user->identity->company->isMedCategory() && Yii::$app->user->can('insuranceCompanyAdmin'),
                    'active'  => strpos(Yii::$app->request->url, '/company/insurance') !== false,
                ],
                [
                    'label'   => Yii::t('app', 'Med Card Teeth Diagnoses'),
                    'url'     => ['/med-card/teeth-diagnosis/index'],
                    'icon'    => 'fa fa-medkit',
                    'active'  => strpos(Yii::$app->request->url, '/med-card/teeth-diagnosis/') !== false,
                    'visible' => Yii::$app->user->identity->company->canManageTeethCare() && Yii::$app->user->can('teethDiagnosisAdmin')
                ],
                [
                    'label'   => Yii::t('app', 'Change password'),
                    'url'     => ['/user/default/password'],
                    'icon'    => 'fa fa-key',
                    'visible' => true
                ],
            ],
            self::STATISTIC => [
                [
                    'label'   => Yii::t('app', 'General'),
                    'icon'    => 'fa fa-chart-pie',
                    'url'     => ['/statistic/index'],
                    'visible' => Yii::$app->user->can('statisticView')
                ],
                [
                    'label'   => Yii::t('app', 'Staff'),
                    'icon'    => 'fa fa-users',
                    'url'     => ['/statistic/staff'],
                    'visible' => Yii::$app->user->can('statisticStaffView')
                ],
                [
                    'label'   => Yii::t('app', 'Services'),
                    'icon'    => 'fa fa-shopping-cart',
                    'url'     => ['/statistic/service'],
                    'visible' => Yii::$app->user->can('statisticServiceView')
                ],
                [
                    'label'   => Yii::t('app', 'Customers'),
                    'icon'    => 'fa fa-child',
                    'url'     => ['/statistic/customer'],
                    'visible' => Yii::$app->user->can('statisticCustomerView')
                ],
                [
                    'label'   => Yii::t('app', 'Insurances'),
                    'icon'    => 'fa fa-child',
                    'url'     => ['/statistic/insurance'],
                    'visible' => Yii::$app->user->can('statisticInsuranceView')
                ],
                [
                    'label'   => Yii::t('app', 'Cost price'),
                    'icon'    => 'fa fa-money-bill-alt',
                    'url'     => ['/statistic/cost'],
                    'visible' => Yii::$app->user->can('statisticServiceView')
                ],
            ],
            self::TIMETABLE => [],
            self::WAREHOUSE => (isset($params['sideNavID']) && $params['sideNavID'] == self::WAREHOUSE)
                ? $params['sideNavOptions'] : []
            ,
        ];
    }

    /**
     * @return array
     */
    public static function moduleOptions(): array
    {
        return [
            self::CUSTOMERS => ['class' => 'customers'],
            self::FINANCE   => ['class' => 'finance'],
            self::ORDERS    => ['class' => 'summary'],
            self::SERVICES  => ['class' => 'services'],
            self::SETTINGS  => ['class' => 'settings'],
            self::STATISTIC => ['class' => 'statistics'],
            self::TIMETABLE => ['class' => 'calendar'],
            self::WAREHOUSE => ['class' => 'stock'],
        ];
    }

    /**
     * @return array
     */
    public static function permissions(): array
    {
        return [
            self::TIMETABLE => true,
            self::CUSTOMERS => [
                'companyCustomerView',
                'companyCustomerCategoryAdmin',
                'companyCustomerLoyaltyAdmin',
                'companyCustomerLostView',
                'companyCustomerSubscriptionAdmin',
                'companyCustomerSourceAdmin',
            ],
            self::ORDERS    => [
                'orderView',
                'staffReviewAdmin',
                'divisionReviewAdmin',
                'customerRequestView',
            ],
            self::FINANCE   => [
                'cashView',
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
            self::STATISTIC => [
                'statisticView',
                'statisticStaffView',
                'statisticServiceView',
                'statisticCustomerView',
                'statisticInsuranceView',
            ],
            self::SERVICES  => [
                'divisionServiceView',
                'divisionServiceCreate',
                'divisionServiceUpdate',
                'divisionServiceDelete'
            ],
            self::SETTINGS  => [
                'staffAdmin',
                'scheduleAdmin',
                'companyPositionAdmin',
                'smsTemplatesAdmin',
                'documentTemplateAdmin',
                'paymentAdmin',
                'webcallAdmin',
                'insuranceCompanyAdmin',
                'teethDiagnosisAdmin'
            ],
            self::WAREHOUSE => ['warehouseAdmin'],
        ];
    }

    /**
     * @return array
     */
    public static function moduleLabels(): array
    {
        return [
            self::CUSTOMERS => Yii::t('app', 'Customers'),
            self::FINANCE   => Yii::t('app', 'Finance'),
            self::ORDERS    => Yii::t('app', 'Summary'),
            self::SERVICES  => Yii::t('app', 'Services'),
            self::SETTINGS  => Yii::t('app', 'Settings'),
            self::STATISTIC => Yii::t('app', 'Statistics'),
            self::TIMETABLE => Yii::t('app', 'Timetable'),
            self::WAREHOUSE => Yii::t('app', 'Warehouse'),
        ];
    }

    /**
     * @return array
     */
    public static function moduleUrls(): array
    {
        return [
            self::CUSTOMERS => ['/customer/customer/index'],
            self::FINANCE   => ['/finance/cash/index'],
            self::ORDERS    => ['/order/order/index'],
            self::SERVICES  => ['/division/service/index'],
            self::SETTINGS  => ['/company/default/update'],
            self::STATISTIC => ['/statistic/index'],
            self::TIMETABLE => ['/timetable/index'],
            self::WAREHOUSE => ['/warehouse/product/index'],
        ];
    }

    /**
     * @param string $url
     * @return array
     */
    private static function getFinanceItems(string $url)
    {
        return [
            [
                'label'   => Yii::t('app', 'Cashes'),
                'icon'    => 'fa fa-credit-card',
                'url'     => ['/finance/cash/index'],
                'active'  => (
                    strpos($url, '/finance/cash/') !== false
                    || strpos($url, '/finance/cash/index') !== false
                    || strpos($url, '/finance/cash/create') !== false
                    || strpos($url, '/finance/cash/view') !== false
                    || strpos($url, '/finance/cash/update') !== false
                    || strpos($url, '/finance/cashflow/create') !== false
                ),
                'visible' => Yii::$app->user->can('cashView')
            ],
            [
                'label'   => Yii::t('app', 'Contractors'),
                'icon'    => 'fa fa-building',
                'url'     => ['/finance/contractor/index'],
                'active'  => (
                    strpos($url, '/finance/contractor/') !== false
                    || strpos($url, '/finance/contractor/index') !== false
                    || strpos($url, '/finance/contractor/create') !== false
                    || strpos($url, '/finance/contractor/view') !== false
                    || strpos($url, '/finance/contractor/update') !== false
                ),
                'visible' => Yii::$app->user->can('companyContractorAdmin')
            ],
            [
                'label'   => Yii::t('app', 'Cost Items Menu'),
                'icon'    => 'fa fa-sign-out-alt',
                'url'     => ['/finance/cost-item/index'],
                'active'  => (
                    strpos($url, '/finance/cost-item/') !== false
                    || strpos($url, '/finance/cost-item/index') !== false
                    || strpos($url, '/finance/cost-item/create') !== false
                    || strpos($url, '/finance/cost-item/view') !== false
                    || strpos($url, '/finance/cost-item/update') !== false
                ),
                'visible' => Yii::$app->user->can('companyCostItemAdmin')
            ],
            [
                'label'   => Yii::t('app', 'Cost Items Category Menu'),
                'icon'    => 'fa fa-sign-out-alt',
                'url'     => ['/finance/cost-item-category/index'],
                'active'  => (
                    strpos($url, '/finance/cost-item-category/') !== false
                    || strpos($url, '/finance/cost-item-category/index') !== false
                    || strpos($url, '/finance/cost-item-category/create') !== false
                    || strpos($url, '/finance/cost-item-category/update') !== false
                ),
                'visible' => Yii::$app->user->can('companyCostItemAdmin')
            ],
            [
                'label'   => Yii::t('app', 'Payroll Schemes'),
                'icon'    => 'fa fa-money-bill-alt',
                'url'     => ['/finance/scheme/index'],
                'active'  => (
                    strpos($url, '/finance/scheme/index') !== false
                    || strpos($url, '/finance/scheme/create') !== false
                    || strpos($url, '/finance/scheme/view') !== false
                    || strpos($url, '/finance/scheme/update') !== false
                ),
                'visible' => Yii::$app->user->can('schemeAdmin')
            ],
            [
                'label'   => Yii::t('app', 'Payroll Staff'),
                'icon'    => 'fa fa-money-bill-alt',
                'url'     => ['/finance/salary/estimate'],
                'active'  => (
                    strpos($url, '/finance/salary/estimate') !== false
                ),
                'visible' => Yii::$app->user->can('salaryPay')
            ],
            [
                'label'   => Yii::t('app', 'Company Cashflows'),
                'icon'    => 'fa fa-money-bill-alt',
                'url'     => ['/finance/cashflow/index'],
                'active'  => (
                    strpos($url, '/finance/cashflow/index') !== false
                    || strpos($url, '/finance/cashflow/update') !== false
                ),
                'visible' => Yii::$app->user->can('companyCashflowAdmin')
            ],
            [
                'label'   => Yii::t('app', 'Salary Report'),
                'icon'    => 'fa fa-chart-line',
                'url'     => ['/finance/salary/index'],
                'active'  => (
                    strpos($url, '/finance/salary/') !== false && strpos($url, '/finance/salary/estimate') === false
                ),
                'visible' => Yii::$app->user->can('salaryReportView')
            ],
            [
                'label'   => Yii::t('app', 'Report for the period'),
                'icon'    => 'fa fa-chart-line',
                'url'     => ['/finance/report/period'],
                'visible' => Yii::$app->user->can('reportPeriodView')
            ],
            [
                'label'   => Yii::t('app', 'Staff report'),
                'icon'    => 'fa fa-chart-line',
                'url'     => ['/finance/report/daily'],
                'visible' => Yii::$app->user->can('reportStaffView')
            ],
            [
                'label'   => Yii::t('app', 'Balance report'),
                'icon'    => 'fa fa-chart-line',
                'url'     => ['/finance/report/balance'],
                'visible' => Yii::$app->user->can('reportBalanceView')
            ],
            [
                'label'   => Yii::t('app', 'Referrer report'),
                'icon'    => 'fa fa-chart-line',
                'url'     => ['/finance/report/referrer'],
                'visible' => Yii::$app->user->can('reportReferrerView')
            ],
            [
                'label'   => Yii::t('app', 'Cashback'),
                'icon'    => 'fa fa-credit-card',
                'url'     => ['/finance/cashback/index'],
                'visible' => Yii::$app->user->can('cashbackAdmin')
            ],
        ];
    }

    /**
     * @param string $url
     * @param array $params
     * @return array
     */
    private static function getCustomerItems(string $url, array $params): array
    {
        if (isset($params['sideNavOptions']) && isset($params['sideNavID']) && $params['sideNavID'] == self::CUSTOMERS) {
            return $params['sideNavOptions'];
        }

        return [
            [
                'label'   => Yii::t('app', 'Customers'),
                'icon'    => 'icon sprite-group',
                'url'     => ['/customer/customer/index'],
                'active'  => (strpos($url, '/customer/customer/index') !== false
                    || strpos($url, '/customer/customer/create') !== false
                    || strpos($url, '/customer/customer/view') !== false
                    || strpos($url, '/customer/customer/update') !== false
                    || strpos($url, '/customer/customer/archive') !== false),
                'visible' => Yii::$app->user->can('companyCustomerView')
            ],
            [
                'label'   => Yii::t('app', 'Customer Categories'),
                'icon'    => 'fa fa-tags',
                'url'     => ['/customer/customer-category/index'],
                'active'  => (strpos($url, '/customer/customer-category/index') !== false
                    || strpos($url, '/customer/customer-category/create') !== false
                    || strpos($url, '/customer/customer-category/view') !== false
                    || strpos($url, '/customer/customer-category/update') !== false),
                'visible' => Yii::$app->user->can('companyCustomerCategoryAdmin')
            ],
            [
                'label'   => Yii::t('app', 'Customer Loyalty'),
                'icon'    => 'fa fa-gem',
                'url'     => ['/customer/customer-loyalty/index'],
                'visible' => Yii::$app->user->can('companyCustomerLoyaltyAdmin')
            ],
            [
                'label'   => Yii::t('app', 'Lost Customers'),
                'icon'    => 'fa fa-users',
                'url'     => ['/customer/customer/lost'],
                'visible' => Yii::$app->user->can('companyCustomerLostView')
            ],
            [
                'label'   => Yii::t('app', 'Season tickets'),
                'icon'    => 'fa fa-address-card',
                'url'     => ['/customer/subscription/index'],
                'active'  => (strpos($url, '/customer/subscription/index') !== false
                    || strpos($url, '/customer/subscription/create') !== false
                    || strpos($url, '/customer/subscription/update') !== false),
                'visible' => Yii::$app->user->can('companyCustomerSubscriptionAdmin')
            ],
            [
                'label'   => Yii::t('app', 'Customer Source'),
                'url'     => ['/customer/source/index'],
                'icon'    => 'fa fa-image',
                'active'  => strpos($url, '/customer/source/index') !== false
                    || strpos($url, '/customer/source/create') !== false
                    || strpos($url, '/customer/source/update') !== false,
                'visible' => Yii::$app->user->can('companySourceAdmin')
            ],
        ];
    }
}