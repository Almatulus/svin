<?php

return [
    'datecontrol' =>  [
        'class' => 'kartik\datecontrol\Module',

        // format settings for displaying each date attribute (ICU format example)
        'displaySettings' => [
            \kartik\datecontrol\Module::FORMAT_DATE => 'dd-MM-yyyy',
            \kartik\datecontrol\Module::FORMAT_TIME => 'hh:mm:ss a',
            \kartik\datecontrol\Module::FORMAT_DATETIME => 'dd/MM/yyyy hh:mm:ss a',
        ],
        // format settings for saving each date attribute (PHP format example)
        'saveSettings' => [
            \kartik\datecontrol\Module::FORMAT_DATE => 'php:Y-m-d', // saves as unix timestamp
            \kartik\datecontrol\Module::FORMAT_TIME => 'php:H:i:s',
            \kartik\datecontrol\Module::FORMAT_DATETIME => 'php:Y-m-d H:i:s',
        ],
        // set your display timezone
        'displayTimezone' => 'Asia/Almaty',

        'ajaxConversion'=>false,
    ],
    'gridview'  => [
        'class' => \kartik\grid\Module::className(),
    ],
    'finance'   => [
        'class' => 'frontend\modules\finance\FinanceModule',
    ],
    'division'  => [
        'class' => 'frontend\modules\division\DivisionModule',
    ],
    'admin'     => [
        'class' => 'frontend\modules\admin\AdminModule',
    ],
    'customer'  => [
        'class' => 'frontend\modules\customer\CustomerModule',
    ],
    'company'   => [
        'class' => 'frontend\modules\company\CompanyModule',
    ],
    'document'  => [
        'class' => 'frontend\modules\document\Module',
    ],
    'med-card'  => [
        'class' => 'frontend\modules\medCard\MedCardModule',
    ],
    'warehouse' => [
        'class' => 'frontend\modules\warehouse\WarehouseModule',
    ],
    'webcall'   => [
        'class' => 'frontend\modules\webcall\WebcallModule',
    ],
    'order'     => [
        'class' => 'frontend\modules\order\OrderModule',
    ],
    'user'      => [
        'class' => 'frontend\modules\user\UserModule',
    ],
];
