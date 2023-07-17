<?php

return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/test.php'),
    require(__DIR__ . '/../../common/config/test-local.php'),
    require(__DIR__ . '/../../api/config/main.php'),
    require(__DIR__ . '/../../api/config/main-local.php'),
    [
        'id' => 'app-api-tests',
        'components' => [
            'db'      => require('db.php'),
            'redis'   => [
                'hostname' => 'redis',
                'port' => 6379,
            ],
            'request' => [
                'enableCookieValidation' => false,
            ],
            's3' => [
                'class' => 'common\components\DummyS3',
                'credentials'   => [
                    'key'    => getenv('AWS_S3_KEY'),
                    'secret' => getenv('AWS_S3_SECRET'),
                ],
                'region'        => getenv('AWS_S3_REGION'),
                'defaultBucket' => getenv('AWS_S3_BUCKET'),
                'defaultAcl'    => 'public-read',
            ],
        ],
    ],
    file_exists(__DIR__ . '/test-local.php') ? require(__DIR__ . '/test-local.php') : []
);
