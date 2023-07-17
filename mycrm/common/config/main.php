<?php

$params = require(__DIR__ . '/../../common/config/params.php');

return [
    'timeZone'            => 'Asia/Almaty',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'container'           => [
        'definitions' => [
            'yii\data\Pagination' => [
                'pageSizeLimit' => [0, 100]
            ]
        ],
    ],
    'bootstrap' => ['queue'],
    'components' => [
        'user' => [
            'absoluteAuthTimeout' => \core\models\user\User::SESSION_TIMEOUT
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOST'),
            'port' => getenv('REDIS_PORT'),
            'database' => 0,
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        'session'                => [
            'class' => 'yii\redis\Session',
            'timeout' => \core\models\user\User::SESSION_TIMEOUT,
        ],
        'authManager'            => [
            'class' => \yii\rbac\DbManager::className(),
            'defaultRoles' => ['user'],
            'itemTable' => '{{%auth_item}}',
            'itemChildTable' => '{{%auth_item_child}}',
            'assignmentTable' => '{{%auth_assignment}}',
            'ruleTable' => '{{%auth_rule}}',
        ],
        'formatter'              => [
            'datetimeFormat'         => 'php:d F Y H:i',
            'dateFormat'             => 'php:d F Y',
            'timeFormat'             => 'php:H:i',
            'defaultTimeZone'        => 'Asia/Almaty',
            'timeZone'               => 'Asia/Almaty',
            'decimalSeparator'       => ',',
            'numberFormatterOptions' => [
                NumberFormatter::MIN_FRACTION_DIGITS => 2,
                NumberFormatter::MAX_FRACTION_DIGITS => 2,
            ],
            'numberFormatterSymbols' => [
                NumberFormatter::CURRENCY_SYMBOL           => '₸',
                NumberFormatter::MONETARY_SEPARATOR_SYMBOL => ',',
            ],
        ],
        'db'                     => require(__DIR__ . '/db.php'),
        'queue'                  => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'queue',
        ],
        'contactService'         => 'core\services\customer\ContactService',
        'companyCashflowService' => 'core\services\CompanyCashflowService',
        'loyaltyManager'         => 'core\services\customer\LoyaltyManager',
        'usageService'           => 'core\services\warehouse\UsageService',
        'userLogger'             => 'core\services\user\Logger',
        'customerService'        => 'core\services\customer\CompanyCustomerService',
    ],
    'params' => $params,
];
