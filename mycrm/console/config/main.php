<?php
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'gii',
        'common\bootstrap\SetUp'
    ],
    'language' => 'ru',
    'timeZone' => 'Asia/Almaty',
    'controllerNamespace' => 'console\controllers',
    'modules' => [
        'gii' => 'yii\gii\Module',
        'customer' => [
            'class' => 'frontend\modules\customer\CustomerModule',
        ],
        'finance' => [
            'class' => 'frontend\modules\finance\FinanceModule',
        ],
//        'division' => [
//            'class' => 'frontend\modules\division\DivisionModule',
//        ],
        'admin' => [
            'class' => 'frontend\modules\admin\AdminModule',
        ],
    ],
    'components' => [
        'googleApiClient' => [
            'class' => 'common\components\GoogleApiClient',
        ],
        'pushService'     => [
            'class'  => 'core\services\PushService',
            'apiKey' => getenv('FIREBASE_KEY')
        ],
        'formatter'       => [
            'datetimeFormat'  => 'd MMMM y HH:mm',
            'dateFormat'      => 'd MMMM y',
            'timeFormat'      => 'HH:mm',
            'defaultTimeZone' => 'Asia/Almaty',
            'timeZone'        => 'Asia/Almaty',
        ],
        'log'             => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'user'            => [
            'class' => 'yii\web\User',
            'identityClass' => 'core\models\user\User',
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'sms' => [
            'class' => '\common\components\SMSC',
            'login' => getenv('SMSC_LOGIN'),
            'password' => getenv('SMSC_PASSWORD'),
        ],
        'i18n'              => [
            'translations' => [
                'app' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                ],
                'yii' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                ],
            ],
        ],
        's3'            => [
            'class'         => 'frostealth\yii2\aws\s3\Service',
            'credentials'   => [
                'key'    => getenv('AWS_S3_KEY'),
                'secret' => getenv('AWS_S3_SECRET'),
            ],
            'region'        => getenv('AWS_S3_REGION'),
            'defaultBucket' => getenv('AWS_S3_BUCKET'),
            'defaultAcl'    => 'public-read',
        ],
    ],
    'params' => require(__DIR__ . '/../../common/config/params.php'),
];
