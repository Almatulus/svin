<?php

namespace core\helpers;

use core\models\Staff;
use DateTime;
use Yii;
use yii\helpers\Html;

class StaffHelper
{
    /**
     * @return array
     */
    public static function getCssClasses()
    {
        return [
            "color1"  => "blue",
            "color16" => "celeste",
            "color12" => "turquoise",
            "color10" => "pink",
            "color15" => "beige",
            "color11" => "ochre",
            "color2"  => "cornflower",
            "color6"  => "plum",
            "color13" => "light blue",
            "color9"  => "pistachio",
            "color7"  => "lavender",
            "color14" => "cream",
            "color8"  => "emerald",
        ];
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            Staff::STATUS_ENABLED  => Yii::t('app', 'disabled'),
            Staff::STATUS_DISABLED => Yii::t('app', 'enabled'),
            Staff::STATUS_FIRED    => Yii::t('app', 'fired'),
        ];
    }

    /**
     * @return array
     */
    public static function getPermissions()
    {
        return [
            'timetableView'        => Yii::t('app', 'Timetable'),
            'companyCustomerOwner' => Yii::t('app', 'Customers'),
            'companyOwner'         => Yii::t('app', 'Company'),
            'orderOwner'           => Yii::t('app', 'Summary'),
            'statisticView'        => Yii::t('app', 'Statistic'),
            'divisionServiceOwner' => Yii::t('app', 'Services'),
            'cashOwner'            => Yii::t('app', 'Finance'),
            'warehouseAdmin'       => Yii::t('app', 'Warehouse'),
        ];
    }

    /**
     * @return array
     */
    public static function getModulePermissions()
    {
        return [
            'timetableView'            => Yii::t('app', 'Timetable'),
            Yii::t('app', 'Customers') => [
                'companyCustomerOwner'             => 'Клиенты',
                'companyCustomerCategoryAdmin'     => 'Категории клиентов',
                'companyCustomerLoyaltyAdmin'      => 'Акции и Скидки',
                'companyCustomerLostView'          => 'Потерянные клиенты',
                'companyCustomerSubscriptionAdmin' => 'Абонементы',
                'companySourceAdmin'               => 'Узнали через',
            ],
            Yii::t('app', 'Summary')   => [
                'orderOwner'          => 'Записи',
                'staffReviewAdmin'    => 'Отзывы о сотрудниках',
                'divisionReviewAdmin' => 'Отзывы о заведениях',
                'customerRequestView' => 'Сообщения',
            ],
            Yii::t('app', 'Finance')   => [
                'cashOwner'              => 'Счета и кассы',
                'companyContractorAdmin' => 'Контрагенты',
                'companyCostItemAdmin'   => 'Статьи платежей',
                'schemeAdmin'            => 'Схемы расчета ЗП',
                'salaryPay'              => 'Выдача ЗП',
                'companyCashflowAdmin'   => 'Движение средств',
                'salaryReportView'       => 'Отчет по зарплате',
                'reportPeriodView'       => 'Отчет за период',
                'reportStaffView'        => 'Отчет по сотрудникам',
                'reportBalanceView'      => 'Отчет по балансу',
                'reportReferrerView'     => 'Отчет по направлениям',
                'cashbackAdmin'          => Yii::t('app', 'Cashback'),
            ],
            Yii::t('app', 'Statistic')  => [
                'statisticView'          => 'Основные',
                'statisticStaffView'     => 'Сотрудники',
                'statisticServiceView'   => 'Услуги',
                'statisticCustomerView'  => 'Клиенты',
                'statisticInsuranceView' => Yii::t('app', 'insurance'),
            ],
            Yii::t('app', 'Services')   => [
                'divisionServiceView'      => 'Просмотр',
                'divisionServiceCreate'    => 'Создание',
                'divisionServiceUpdateOwn' => 'Редактирование',
                'divisionServiceDeleteOwn' => 'Удаление',
            ],
            Yii::t('app', 'Categories') => [
                'serviceCategoryCreate' => 'Создание',
                'serviceCategoryUpdate' => 'Редактирование',
                'serviceCategoryDelete' => 'Удаление',
            ],
            'warehouseAdmin'            => Yii::t('app', 'Warehouse'),
            Yii::t('app', 'Settings')   => [
                'companyOwner'          => 'Информация',
                'staffAdmin'            => 'Сотрудники',
                'scheduleAdmin'         => 'График сотрудников',
                'companyPositionAdmin'  => 'Должности',
                'smsTemplatesAdmin'     => 'Шаблоны SMS',
                'paymentAdmin'          => 'Оплаты',
                'webcallAdmin'          => 'Звонки',
                'insuranceCompanyAdmin' => 'Страховки',
                'teethDiagnosisAdmin'   => Yii::t('app', 'Med Card Teeth Diagnoses')
            ],
        ];
    }

    /**
     * @param array $user_permissions
     *
     * @return array
     */
    public static function getPermissionsTree($user_permissions = [])
    {
        $permissions = self::getModulePermissions();

        return self::generatePermissionsTreeList(
            $permissions,
            $user_permissions
        );
    }

    /**
     * @param array $permissions
     * @param array $user_permissions
     *
     * @return array
     */
    private static function generatePermissionsTreeList(
        $permissions,
        $user_permissions
    ): array {
        $result = [];

        foreach ($permissions as $key => $value) {
            if (is_array($value)) {
                $children = [];
                foreach ($value as $child_key => $child) {
                    $children[] = [
                        'title'    => $child,
                        'key'      => $child_key,
                        'expanded' => false,
                        'folder'   => true,
                        'selected' => isset($user_permissions[$child_key]),
                    ];
                }

                $access = [
                    'title'    => Html::tag('b', $key),
                    'expanded' => false,
                    'folder'   => true,
                    'children' => $children
                ];
            } else {
                $access = [
                    'title'    => Html::tag('b', $value),
                    'key'      => $key,
                    'folder'   => true,
                    'selected' => isset($user_permissions[$key])
                ];
            }

            $result[] = $access;
        }

        return [
            [
                'title'    => '<b>' . Yii::t('app', 'All') . '</b>',
                'expanded' => true,
                'folder'   => true,
                'children' => $result
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getPermissionsList()
    {
        return [
            'timetableView',

            'companyCustomerOwner',
            'companyCustomerCategoryAdmin',
            'companyCustomerLoyaltyAdmin',
            'companyCustomerLostView',
            'companyCustomerSubscriptionAdmin',
            'companySourceAdmin',

            'orderOwner',
            'staffReviewAdmin',
            'divisionReviewAdmin',
            'customerRequestView',

            'companyOwner',
            'staffAdmin',
            'scheduleAdmin',
            'companyPositionAdmin',
            'smsTemplatesAdmin',
            'documentTemplateAdmin',
            'paymentAdmin',
            'webcallAdmin',
            'insuranceCompanyAdmin',
            'teethDiagnosisAdmin',

            'statisticView',
            'statisticStaffView',
            'statisticServiceView',
            'statisticCustomerView',
            'statisticInsuranceView',

            'divisionServiceView',
            'divisionServiceCreate',
            'divisionServiceUpdateOwn',
            'divisionServiceDeleteOwn',

            'serviceCategoryCreate',
            'serviceCategoryUpdate',
            'serviceCategoryDelete',

            'cashOwner',
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

            'warehouseAdmin',

            'administrator',
        ];
    }

    /**
     * @param integer  $division_id
     * @param DateTime $date
     *
     * @return string
     */
    public static function getScheduleCacheKey($division_id, \DateTime $date)
    {
        return YII_ENV . '_SCHEDULE_' . $division_id . '_' . $date->format('Y-m-d');
    }
}
