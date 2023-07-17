<?php

$modules = require(__DIR__ . '/../config/modules.php');
$mailer  = require(__DIR__ . '/../../common/config/mailer.php');

$config = [
    'id'                  => 'app-api-customer',
    'basePath'            => dirname(__DIR__),
    'container'           => [
        'definitions' => [
            'yii\data\Pagination' => [
                'pageSizeLimit' => [0, 100],
                'pageSizeParam' => 'pagination',
                'validatePage'  => false
            ]
        ],
    ],
    'bootstrap'           => [
        'log',
        'common\bootstrap\SetUp'
    ],
    'name'                => 'MyCRM',
    'language'            => 'ru',
    'timeZone'            => 'Asia/Almaty',
    'controllerNamespace' => 'api\controllers',
    'modules'             => $modules,
    'components'          => [
        'user'          => [
            'identityClass' => 'core\models\user\User',
            'enableSession' => false,
            'loginUrl'      => null,
        ],
        'request'       => [
            'enableCookieValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/xml'         => 'yii\web\XmlParser',
            ]
        ],
        'response'    => [
            'format'     => 'json',
            'formatters' => [
                'json' => [
                    'class'         => \yii\web\JsonResponseFormatter::className(),
                    'prettyPrint'   => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        's3'          => [
            'class'         => 'frostealth\yii2\aws\s3\Service',
            'credentials'   => [
                'key'    => getenv('AWS_S3_KEY'),
                'secret' => getenv('AWS_S3_SECRET'),
            ],
            'region'        => getenv('AWS_S3_REGION'),
            'defaultBucket' => getenv('AWS_S3_BUCKET'),
            'defaultAcl'    => 'public-read',
        ],
        'pushService' => [
            'class'  => 'core\services\PushService',
            'apiKey' => getenv('FIREBASE_KEY')
        ],
        'log'         => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'i18n'        => [
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
        'apiUrlManager' => require __DIR__ . '/urlManager.php',
        'urlManager'    => function () {
            return Yii::$app->get('apiUrlManager');
        },
        'sms' => [
            'class' => '\common\components\SMSC',
            'login' => getenv('SMSC_LOGIN'),
            'password' => getenv('SMSC_PASSWORD'),
        ],
        'mailer' => $mailer,
        'excel' => [
            'class' => '\common\components\excel\Excel',
        ],
    ],
];

return $config;
