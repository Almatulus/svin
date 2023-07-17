<?php

$modules = require(__DIR__ . '/../config/modules.php');
$mailer  = require(__DIR__ . '/../../common/config/mailer.php');

$config = [
    'id'                  => 'app-backend',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => [
        'log',
        'common\bootstrap\SetUp'
    ],
    'name'                => 'MyCRM',
    'language'            => 'ru',
    'timeZone'            => 'Asia/Almaty',
    'defaultRoute'        => 'timetable/index',
    'controllerNamespace' => 'frontend\controllers',
    'components'          => [
        'googleApiClient'   => [
            'class' => 'common\components\GoogleApiClient',
        ],
        's3'                => [
            'class'         => 'frostealth\yii2\aws\s3\Service',
            'credentials'   => [
                'key'    => getenv('AWS_S3_KEY'),
                'secret' => getenv('AWS_S3_SECRET'),
            ],
            'region'        => getenv('AWS_S3_REGION'),
            'defaultBucket' => getenv('AWS_S3_BUCKET'),
            'defaultAcl'    => 'public-read',
        ],
        'request'           => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Xgg20I36WBG9VtVspEIeFkb_vccPHWKb',
            'parsers'             => [
                'application/json' => 'yii\web\JsonParser',
                'text/xml'         => 'yii\web\XmlParser',
            ]
        ],
        'reCaptcha'         => [
            'name'    => 'reCaptcha',
            'class'   => 'himiklab\yii2\recaptcha\ReCaptcha',
            'siteKey' => getenv('RE_CAPTCHA_PUBLIC'),
            'secret'  => getenv('RE_CAPTCHA_PRIVATE'),
        ],
        'user'              => [
            'identityClass' => 'core\models\user\User',
            'loginUrl'      => ['auth/login'],
            'authTimeout'   => 60 * 60 * 24 * 5 // 8 hours in seconds
        ],
        'errorHandler'      => [
            'errorAction' => 'site/error',
        ],
        'log'               => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
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
        'backendUrlManager' => require __DIR__ . '/urlManager.php',
        'urlManager'        => function () {
            return Yii::$app->get('backendUrlManager');
        },
        'view'              => [
            'theme' => [
                'pathMap' => [
                    '@app/views/layouts' => '@app/views/layout'
                ],
            ],
        ],
        'apns'              => [
            'class'       => 'bryglen\apnsgcm\Apns',
            'environment' => \bryglen\apnsgcm\Apns::ENVIRONMENT_SANDBOX,
            'pemFile'     => dirname(__FILE__) . '/apnssert/apns-dev.pem',
            // 'retryTimes' => 3,
            'options'     => [
                'sendRetryTimes' => 5
            ]
        ],
        'assetManager'      => [
            'appendTimestamp' => true,
        ],
        'sms'               => [
            'class'    => '\common\components\SMSC',
            'login' => getenv('SMSC_LOGIN'),
            'password' => getenv('SMSC_PASSWORD'),
        ],
        'excel'             => [
            'class' => '\common\components\excel\Excel',
        ],
        'productParser'     => [
            'class' => '\common\components\parsers\ProductParser',
        ],
        'serviceParser'     => [
            'class' => '\common\components\parsers\ServiceParser',
        ],
        'mailer'            => $mailer,
    ],
    'modules'             => $modules
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['bootstrap'][] = 'gii';

    $config['modules']['debug'] = [
        'class'      => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];

    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
        'allowedIPs' => ['*'],
    ];

    $config['components']['assetManager']['forceCopy'] = true;
}

return $config;
