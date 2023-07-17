<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => 'utf8',
    'tablePrefix' => getenv('DB_TABLE_PREFIX'),
    'schemaMap' => [
        'pgsql' => [
            'class'             => 'yii\db\pgsql\Schema',
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

