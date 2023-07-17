<?php

$config = [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=postgres;dbname=db_mycrm_test',
    'username' => 'db_user_mycrm',
    'password' => 'password',
    'charset' => 'utf8',
    'tablePrefix' => 'crm_',
    'schemaMap' => [
        'pgsql'=> [
            'class'             =>'yii\db\pgsql\Schema',
            'defaultSchema'     => 'public',
            'columnSchemaClass' => [
                'class'                                   => \yii\db\pgsql\ColumnSchema::class,
                'disableJsonSupport'                      => true,
                'disableArraySupport'                     => true,
                'deserializeArrayColumnToArrayExpression' => false,
            ],
        ]
    ],
];

if (file_exists(__DIR__ . '/db.local.php')) {
    $config = \yii\helpers\ArrayHelper::merge($config, require(__DIR__ . '/db.local.php'));
}

return $config;

