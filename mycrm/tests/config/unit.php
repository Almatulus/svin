<?php
/**
 * Application configuration for unit tests
 */
return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/config.php'),
    [
        'id'         => 'unit-app',
        'basePath'   => dirname(__DIR__),
        'components' => [
            'redis' => [
                'class'    => 'yii\redis\Connection',
                'hostname' => "redis",
                'port'     => 6379,
                'database' => 0,
            ],
            'user'  => [
                'identityClass' => 'core\models\user\User',
                'authTimeout'   => 60 * 60 * 24 * 5, // 8 hours in seconds,
            ],
            'request' => [
                'enableCookieValidation' => false,
            ],
            's3' => [
                'class'         => 'common\components\DummyS3',
                'credentials'   => [
                    'key'    => getenv('AWS_S3_KEY'),
                    'secret' => getenv('AWS_S3_SECRET'),
                ],
                'region'        => getenv('AWS_S3_REGION'),
                'defaultBucket' => getenv('AWS_S3_BUCKET'),
                'defaultAcl'    => 'public-read',
            ],
        ],
    ]
);
