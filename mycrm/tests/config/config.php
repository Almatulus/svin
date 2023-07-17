<?php
/**
 * Application configuration shared by all test types
 */
$db = require(__DIR__ . '/db.php');

return [
    'language' => 'ru',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            'fixtureDataPath' => '@tests/fixtures',
            'templatePath' => '@tests/templates',
            'namespace' => 'app\tests\fixtures',
        ],
    ],
    'components' => [
        'db' => $db,
        'googleApiClient' => [
            'class' => 'common\components\GoogleApiClient',
            'redirectUri' => "http://localhost:8080/oauth2/index/"
        ],
        'mailer' => [
            'useFileTransport' => true,
        ],
    ],
    'params' => require(__DIR__ . '/params.php'),
];
