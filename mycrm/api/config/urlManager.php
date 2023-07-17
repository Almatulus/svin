<?php
/** @var array $params */
return [
    'class'               => 'yii\web\UrlManager',
    'baseUrl'             => '',
    'enablePrettyUrl'     => true,
    'enableStrictParsing' => false,
    'showScriptName'      => false,
    'rules'               => [

        'v1/<c:\w+>'         => 'v1/<c>/index',
        'v1/<c:\w+>/<i:\d+>' => 'v1/<c>/view',
        'v1/<c:\w+>/<a:\w+>' => 'v1/<c>/<a>',

        // Guest
        'POST v2/user/login' => 'v2/user/auth/login',
        'v2/user/login'      => 'v2/user/auth/options',

        'POST v2/user/forgot-password' => 'v2/user/auth/forgot-password',
        'v2/user/forgot-password'      => 'v2/user/auth/options',

        'POST v2/user/validate-code' => 'v2/user/auth/validate-code',
        'v2/user/validate-code'      => 'v2/user/auth/options',

        'POST v2/user/change-password' => 'v2/user/auth/change-password',
        'v2/user/change-password'      => 'v2/user/auth/options',

        'GET,HEAD v2/country'                                => 'v2/country/default/index',
        'GET,HEAD v2/country/<id:\d+>'                       => 'v2/country/default/view',
        'GET,HEAD v2/country/<country_id:\d+>/city'          => 'v2/country/city/index',
        'GET,HEAD v2/country/<country_id:\d+>/city/<id:\d+>' => 'v2/country/city/view',

        'GET,HEAD v2/service/root-category'          => 'v2/service/root-category/index',
        'GET,HEAD v2/service/root-category/<id:\d+>' => 'v2/service/root-category/view',

        'GET,HEAD v2/payment'          => 'v2/payment/default/index',
        'GET,HEAD v2/payment/<id:\d+>' => 'v2/payment/default/view',

        'POST v2/customer/request/smsc-callback' => 'v2/customer/request/smsc-callback',
        'v2/customer/request'      => 'v2/customer/request/options',

        // Authorized
        'POST v2/user/logout' => 'v2/user/auth/logout',

        'GET,HEAD v2/user/permission' => 'v2/user/permission/index',
        'v2/user/permission'          => 'v2/user/permission/options',

        'GET,HEAD v2/user' => 'v2/user/auth/user',
        'v2/user'          => 'v2/user/auth/options',

        'GET,HEAD v2/document'               => 'v2/document/default/index',
        'GET,HEAD v2/document/<id:\d+>'      => 'v2/document/default/view',
        'PUT,PATCH v2/document/<id:\d+>'     => 'v2/document/default/update',
        'POST v2/document/<id:\d+>'          => 'v2/document/default/create',
        'GET v2/document/generate/<id:\d+>'  => 'v2/document/default/generate',

        'v2/document'                   => 'v2/document/default/options',
        'v2/document/<id:\d+>'          => 'v2/document/default/options',
        'v2/document/generate/<id:\d+>' => 'v2/document/default/options',

        'GET,HEAD v2/document/form'          => 'v2/document/form/index',
        'GET,HEAD v2/document/form/<id:\d+>' => 'v2/document/form/view',

        'GET,HEAD v2/document/template'           => 'v2/document/template/index',
        'GET,HEAD v2/document/template/<id:\d+>'  => 'v2/document/template/view',
        'POST v2/document/template/<id:\d+>'      => 'v2/document/template/create',
        'PUT,PATCH v2/document/template/<id:\d+>' => 'v2/document/template/update',
        'DELETE v2/document/template/<id:\d+>'    => 'v2/document/template/delete',
        'v2/document/template'                    => 'v2/document/template/options',
        'v2/document/template/<id:\d+>'           => 'v2/document/template/options',

        'GET,HEAD v2/order'                            => 'v2/order/default/index',
        'GET,HEAD v2/order/<id:\d+>'                   => 'v2/order/default/view',
        'POST v2/order'                                => 'v2/order/default/create',
        'PUT,PATCH v2/order/<id:\d+>'                  => 'v2/order/default/update',
        'DELETE v2/order/<id:\d+>'                     => 'v2/order/default/delete',
        'POST v2/order/export'                         => 'v2/order/default/export',
        'GET,HEAD v2/order/<id:\d+>/history'           => 'v2/order/default/history',
        'POST v2/order/checkout/<id:\d+>'              => 'v2/order/default/checkout',
        'POST v2/order/cancel/<id:\d+>'                => 'v2/order/default/cancel',
        'POST v2/order/return/<id:\d+>'                => 'v2/order/default/return',
        'POST v2/order/enable/<id:\d+>'                => 'v2/order/default/enable',
        'POST v2/order/update-duration/<id:\d+>'       => 'v2/order/default/update-duration',
        'POST v2/order/drop/<id:\d+>'                  => 'v2/order/default/drop',
        'POST v2/order/overlapping'                    => 'v2/order/default/overlapping',
        'POST v2/order/<order_id:\d+>/file'            => 'v2/order/file/upload',
        'DELETE v2/order/<order_id:\d+>/file/<id:\d+>' => 'v2/order/file/delete',
        'v2/order/update-duration/<id:\d+>'            => 'v2/order/default/options',

        'GET,HEAD v2/order/events'    => 'v2/timetable/order/events',
        'v2/order/events'             => 'v2/timetable/order/options',
        'GET,HEAD v2/staff/resources' => 'v2/timetable/staff/resources',
        'v2/staff/resources'          => 'v2/timetable/staff/options',

        'GET,HEAD v2/order/<order_id:\d+>/document'          => 'v2/order/document/index',
        'POST v2/order/<order_id:\d+>/document'              => 'v2/order/document/create',
        'v2/order/<order_id:\d+>/document'                   => 'v2/order/document/options',
        'GET,HEAD v2/order/<order_id:\d+>/document/<id:\d+>' => 'v2/order/document/view',
        'v2/order/<order_id:\d+>/document/<id:\d+>'          => 'v2/order/document/options',

        'GET,HEAD v2/document/tooth/<number:\d+>' => 'v2/document/dental-card/index',
        'v2/document/tooth/<number:\d+>'          => 'v2/document/dental-card/options',

        'v2/order'                                     => 'v2/order/default/options',
        'v2/order/<a:\w+>'                             => 'v2/order/default/options',
        'v2/order/<a:\w+>/<id:\d+>'                    => 'v2/order/default/options',
        'v2/order/<id:\d+>/<a:\w+>'                    => 'v2/order/default/options',

        'GET,HEAD v2/pending-order'              => 'v2/order/pending/index',
        'POST v2/pending-order'                  => 'v2/order/pending/create',
        'GET,HEAD v2/pending-order/<id:\d+>'     => 'v2/order/pending/view',
        'PUT,PATCH v2/pending-order/<id:\d+>'    => 'v2/order/pending/update',
        'DELETE v2/pending-order/<id:\d+>'       => 'v2/order/pending/delete',
        'POST v2/pending-order/enabled/<id:\d+>' => 'v2/order/pending/enable',

        'v2/pending-order'                  => 'v2/order/pending/options',
        'v2/pending-order/<id:\d+>'         => 'v2/order/pending/options',
        'v2/pending-order/enabled/<id:\d+>' => 'v2/order/pending/options',

        'GET,HEAD v2/order/document-template'          => 'v2/order/document-template/index',
        'v2/order/document-template'                   => 'v2/order/document-template/options',
        'GET,HEAD v2/order/document-template/<id:\d+>' => 'v2/order/document-template/view',
        'v2/order/document-template/<id:\d+>'          => 'v2/order/document-template/options',

        'GET,HEAD v2/user/division'           => 'v2/user/division/index',
        'GET,HEAD v2/user/division/<id:\d+>'  => 'v2/user/division/view',
        'POST v2/user/division'               => 'v2/user/division/create',
        'PUT,PATCH v2/user/division/<id:\d+>' => 'v2/user/division/update',
        'v2/user/division'                    => 'v2/user/division/options',
        'v2/user/division/<id:\d+>'           => 'v2/user/division/options',

        'GET,HEAD v2/user/company'  => 'v2/user/company/index',
        'PUT,PATCH v2/user/company' => 'v2/user/company/update',
        'v2/user/company'           => 'v2/user/company/options',

        'POST v2/user/push/key'            => 'v2/user/push/key',
        'GET,HEAD v2/user/push/test'       => 'v2/user/push/test',
        'GET,HEAD v2/user/company/balance' => 'v2/user/company/balance',

        'GET,HEAD v2/user/sms-template'  => 'v2/user/sms-template/index',
        'PUT,PATCH v2/user/sms-template' => 'v2/user/sms-template/update',
        'v2/user/sms-template'           => 'v2/user/sms-template/options',

        'GET,HEAD v2/user/staff' => 'v2/user/staff/index',
        'POST v2/user/staff'     => 'v2/user/staff/create',
        'v2/user/staff'          => 'v2/user/staff/options',

        'GET,HEAD v2/user/staff/<id:\d+>'            => 'v2/user/staff/view',
        'PUT,PATCH v2/user/staff/<id:\d+>'           => 'v2/user/staff/update',
        'DELETE v2/user/staff/<id:\d+>'              => 'v2/user/staff/fire',
        'POST v2/user/staff/<id:\d+>/service/add'    => 'v2/user/staff/add-services',
        'POST v2/user/staff/<id:\d+>/service/delete' => 'v2/user/staff/delete-services',
        'v2/user/staff/<id:\d+>'                     => 'v2/user/staff/options',

        'GET,HEAD v2/user/schedule'      => 'v2/user/schedule/index',
        'GET,HEAD v2/user/schedule/week' => 'v2/user/schedule/week',
        'POST v2/user/schedule'          => 'v2/user/schedule/create',
        'PUT,PATCH v2/user/schedule'     => 'v2/user/schedule/update',
        'DELETE v2/user/schedule'        => 'v2/user/schedule/delete',
        'v2/user/schedule'               => 'v2/user/schedule/options',
        'v2/user/schedule/week'          => 'v2/user/schedule/options',

        'GET,HEAD v2/staff/<id:\d+>'  => 'v2/staff/default/view',
        'v2/staff/<id:\d+>'           => 'v2/staff/default/options',

        'GET,HEAD v2/staff/<id:\d+>/review'  => 'v2/staff/review/index',
        'POST v2/staff/<id:\d+>/review'      => 'v2/staff/review/create',
        'PUT,PATCH v2/staff/<id:\d+>/review' => 'v2/staff/review/update',
        'v2/staff/<id:\d+>/review'           => 'v2/staff/review/options',

        'GET,HEAD v2/division/<division_id:\d+>/staff/<staff_id:\d+>/categories' => 'v2/staff/category',
        'v2/division/<division_id:\d+>/staff/<staff_id:\d+>/categories'          => 'v2/staff/category/options',

        'GET,HEAD v2/division/<division_id:\d+>/staff/<staff_id:\d+>/schedule' => 'v2/staff/schedule/index',
        'v2/division/<division_id:\d+>/staff/<staff_id:\d+>/schedule'          => 'v2/staff/schedule/options',

        'GET,HEAD v2/division/service'                                        => 'v2/division/service/index',
        'GET,HEAD v2/division/service/<id:\d+>'                               => 'v2/division/service/view',
        'GET,HEAD v2/division/<division_id:\d+>/staff/<staff_id:\d+>/service' => 'v2/division/service/index',
        'v2/division/<division_id:\d+>/staff/<staff_id:\d+>/service'          => 'v2/division/service/options',

        'GET,HEAD v2/division/<division_id:\d+>/payment' => 'v2/division/payment/index',
        'v2/division/<division_id:\d+>/payment'          => 'v2/division/payment/options',

        'GET,HEAD v2/staff/<staff_id:\d+>/schedule/template' => 'v2/staff/schedule-template/index',
        'POST v2/staff/<staff_id:\d+>/schedule/template'     => 'v2/staff/schedule-template/generate',
        'v2/staff/<staff_id:\d+>/schedule/template'          => 'v2/staff/schedule-template/options',
        'GET,HEAD v2/staff/<staff_id:\d+>/document/form'     => 'v2/staff/document-form/index',
        'v2/staff/<staff_id:\d+>/document/form'              => 'v2/staff/document-form/options',

        'GET,HEAD v2/company/position' => 'v2/company/position/index',
        'POST v2/company/position'     => 'v2/company/position/create',
        'v2/company/position'          => 'v2/company/position/options',

        'GET,HEAD v2/company/position/<id:\d+>'  => 'v2/company/position/view',
        'PUT,PATCH v2/company/position/<id:\d+>' => 'v2/company/position/update',
        'v2/company/position/<id:\d+>'           => 'v2/company/position/options',

        'GET,HEAD v2/service/category' => 'v2/company/category/index',
        'POST v2/service/category'     => 'v2/company/category/create',
        'v2/service/category'          => 'v2/company/category/options',

        'GET,HEAD v2/service/category/<id:\d+>'  => 'v2/company/category/view',
        'PUT,PATCH v2/service/category/<id:\d+>' => 'v2/company/category/update',
        'DELETE v2/service/category/<id:\d+>'    => 'v2/company/category/delete',
        'v2/service/category/<id:\d+>'           => 'v2/company/category/options',

        'GET,HEAD v2/company/payment' => 'v2/company/payment/index',
        'v2/company/payment'          => 'v2/company/payment/options',

        'GET,HEAD v2/company/payment/<id:\d+>' => 'v2/company/payment/view',
        'v2/company/payment/<id:\d+>'          => 'v2/company/payment/options',

        'GET,HEAD v2/company/cash' => 'v2/company/cash/index',
        'v2/company/cash'          => 'v2/company/cash/options',

        'GET,HEAD v2/company/cash/<id:\d+>' => 'v2/company/cash/view',
        'v2/company/cash/<id:\d+>'          => 'v2/company/cash/options',

        'GET,HEAD v2/product'            => 'v2/warehouse/product/index',
        'GET,HEAD v2/product/categories' => 'v2/warehouse/product/categories',
        'v2/product'                     => 'v2/warehouse/product/options',

        'GET,HEAD v2/product/<id:\d+>' => 'v2/warehouse/product/view',
        'v2/product/<id:\d+>'          => 'v2/warehouse/product/options',

        'GET,HEAD v2/product/category' => 'v2/warehouse/category/index',
        'POST v2/product/category'     => 'v2/warehouse/category/create',
        'v2/product/category'          => 'v2/warehouse/category/options',

        'GET,HEAD v2/product/category/<id:\d+>'  => 'v2/warehouse/category/view',
        'PUT,PATCH v2/product/category/<id:\d+>' => 'v2/warehouse/category/update',
        'DELETE v2/product/category/<id:\d+>'    => 'v2/warehouse/category/delete',
        'v2/warehouse/product/<id:\d+>'          => 'v2/warehouse/category/options',

        'GET,HEAD v2/customer' => 'v2/customer/default/index',
        'GET,HEAD v2/customer/lost' => 'v2/customer/default/lost',
        'POST v2/customer'     => 'v2/customer/default/create',
        'v2/customer'          => 'v2/customer/default/options',

        'GET,HEAD v2/customer/<id:\d+>/history' => 'v2/customer/default/history',
        'POST v2/customer/<id:\d+>/merge'       => 'v2/customer/default/merge',
        'POST v2/customer/<id:\d+>/upload-avatar'       => 'v2/customer/default/upload-avatar',

        'POST v2/customer/export' => 'v2/customer/default/export',
        'POST v2/customer/import' => 'v2/customer/default/import',
        'GET,HEAD v2/customer/archive' => 'v2/customer/default/archive',

        'POST v2/customer/multiple/delete' => 'v2/customer/default/delete-multiple',
        'POST v2/customer/multiple/send-request' => 'v2/customer/default/send-request-multiple',
        'POST v2/customer/multiple/add-categories' => 'v2/customer/default/add-categories-multiple',

        'GET,HEAD v2/customer/<id:\d+>' => 'v2/customer/default/view',
        'PUT v2/customer/<id:\d+>'      => 'v2/customer/default/update',
        'v2/customer/<id:\d+>'          => 'v2/customer/default/options',

        'GET,HEAD v2/customer/<customer_id:\d+>/contact' => 'v2/customer/contact/index',
        'v2/customer/<customer_id:\d+>/contact'          => 'v2/customer/contact/options',

        'GET,HEAD v2/customer/category' => 'v2/customer/category/index',
        'POST v2/customer/category'     => 'v2/customer/category/create',
        'v2/customer/category'          => 'v2/customer/category/options',

        'GET,HEAD v2/customer/category/<id\d+>' => 'v2/customer/category/view',
        'PUT,PATCH v2/customer/category/<id:\d+>' => 'v2/customer/category/update',
        'v2/customer/category/<id\d+>'          => 'v2/customer/category/options',

        'GET,HEAD v2/customer/loyalty' => 'v2/customer/loyalty/index',
        'POST v2/customer/loyalty'     => 'v2/customer/loyalty/create',
        'v2/customer/loyalty'          => 'v2/customer/loyalty/options',

        'GET,HEAD v2/customer/loyalty/<id\d+>' => 'v2/customer/loyalty/view',
        'PUT,PATCH v2/customer/loyalty/<id:\d+>' => 'v2/customer/loyalty/update',
        'v2/customer/loyalty/<id\d+>'          => 'v2/customer/loyalty/options',

        'GET,HEAD v2/customer/source' => 'v2/customer/source/index',
        'POST v2/customer/source'     => 'v2/customer/source/create',
        'v2/customer/source'          => 'v2/customer/source/options',

        'GET,HEAD v2/customer/source/<id\d+>' => 'v2/customer/source/view',
        'PUT,PATCH v2/customer/source/<id\d+>' => 'v2/customer/source/update',
        'PUT,PATCH v2/customer/source/<id:\d+>/destination/<destination_id:\d+>' => 'v2/customer/source/move',
        'v2/customer/source/<id\d+>'          => 'v2/customer/source/options',

        'GET,HEAD v2/insurance-company'  => 'v2/company/insurance/index',
        'PUT,PATCH v2/insurance-company' => 'v2/company/insurance/update',
        'v2/insurance-company'           => 'v2/company/insurance/options',

//        'GET,HEAD v2/insurance/<id\d+>' => 'v2/company/insurance/view',
//        'v2/insurance/<id\d+>'          => 'v2/company/insurance/options',

        'GET,HEAD v2/diagnosis' => 'v2/diagnosis/default/index',
        'v2/diagnosis' => 'v2/diagnosis/default/options',

        'GET,HEAD v2/tooth-diagnosis' => 'v2/toothDiagnosis/default/index',
        'POST v2/tooth-diagnosis'     => 'v2/toothDiagnosis/default/create',
        'v2/tooth-diagnosis'          => 'v2/toothDiagnosis/default/options',

        'GET,HEAD v2/tooth-diagnosis/<id\d+>'  => 'v2/toothDiagnosis/default/view',
        'PUT,PATCH v2/tooth-diagnosis/<id\d+>' => 'v2/toothDiagnosis/default/update',
        'DELETE v2/tooth-diagnosis/<id\d+>'    => 'v2/toothDiagnosis/default/delete',
        'v2/tooth-diagnosis/<id\d+>'           => 'v2/toothDiagnosis/default/options',

        'GET,HEAD v2/statistic'                  => 'v2/statistic/default/index',
        'GET,HEAD v2/statistic/staff'            => 'v2/statistic/staff/default/index',
        'GET,HEAD v2/statistic/staff/top'        => 'v2/statistic/staff/default/top',
        'GET,HEAD v2/statistic/service'          => 'v2/statistic/service/default/index',
        'GET,HEAD v2/statistic/export-service'   => 'v2/statistic/service/default/export',
        'GET,HEAD v2/statistic/service/top'      => 'v2/statistic/service/default/top',
        'GET,HEAD v2/statistic/customer'         => 'v2/statistic/customer/default',
        'GET,HEAD v2/statistic/customer/top'     => 'v2/statistic/customer/default/top',
        'GET,HEAD v2/statistic/insurance'        => 'v2/statistic/insurance/default',
        'GET,HEAD v2/statistic/insurance/export' => 'v2/statistic/insurance/default/export',
        'GET,HEAD v2/statistic/calls'            => 'v2/webcall/default/statistics',

        'GET,HEAD v2/news-log' => 'v2/newsLog/default/index',
        'v2/news-log' => 'v2/newsLog/default/options',

        'GET,HEAD v2/app/ios' => 'v2/system/app/ios',
        'GET,HEAD v2/app/android' => 'v2/system/app/android',
        'v2/app/ios'          => 'v2/system/app/options',

        'POST v2/company/referrer' => 'v2/company/referrer/create',
        'GET v2/company/referrer'  => 'v2/company/referrer/index',
        'v2/company/referrer'      => 'v2/company/referrer/options',

        'GET,HEAD v2/company/referrer/<id:\d+>' => 'v2/company/referrer/view',
        'v2/company/referrer/<id:\d+>'          => 'v2/company/referrer/options',

        'GET,HEAD v2/cashflow'                       => 'v2/cashflow/default/index',
        'v2/cashflow'                                => 'v2/cashflow/default/options',

        'GET,HEAD v2/public/staff'                        => 'v2/common/staff/index',
        'v2/public/staff'                                 => 'v2/common/staff/options',
        'GET,HEAD v2/public/staff/<staff_id:\d+>/service' => 'v2/common/service/index',
        'v2/public/staff/<staff_id:\d+>/service'          => 'v2/common/service/options',
        'GET,HEAD v2/public/schedule'                     => 'v2/common/schedule/index',
        'v2/public/schedule'                              => 'v2/common/schedule/options',
        'POST v2/public/order'                            => 'v2/common/order/create',
        'v2/public/order'                                 => 'v2/common/order/options',

        // Irregular controllers
        'PUT,PATCH <m:\w+>/<c:\w+>/<a:\w+>/<id:\d+>' => '<m>/<c>/<a>/update',
        'POST <m:\w+>/<c:\w+>/<a:\w+>'               => '<m>/<c>/<a>/create',
        'GET,HEAD <m:\w+>/<c:\w+>/<a:\w+>'           => '<m>/<c>/<a>/index',
        '<m:\w+>/<c:\w+>/<a:\w+>'                    => '<m>/<c>/<a>/options',

        // Default controllers

        'PUT,PATCH <m:\w+>/<c:\w+>' => '<m>/<c>/default/update',
        'POST <m:\w+>/<c:\w+>'      => '<m>/<c>/default/create',
        'GET,HEAD <m:\w+>/<c:\w+>'  => '<m>/<c>/default/index',
        '<m:\w+>/<c:\w+>'           => '<m>/<c>/default/options',

        'PUT,PATCH <m:\w+>/<c:\w+>/<i:\d+>' => '<m>/<c>/default/update',
        'DELETE <m:\w+>/<c:\w+>/<i:\d+>'    => '<m>/<c>/default/delete',
        'GET,HEAD <m:\w+>/<c:\w+>/<i:\d+>'  => '<m>/<c>/default/view',
        '<m:\w+>/<c:\w+>/<i:\d+>'           => '<m>/<c>/default/options',

        // Subaction controllers

        'PUT,PATCH <m:\w+>/<c:\w+>/<i:\d+>/<a:\w+>' => '<m>/<c>/<a>/update',
        'POST <m:\w+>/<c:\w+>/<i:\d+>/<a:\w+>'      => '<m>/<c>/<a>/create',
        'GET,HEAD <m:\w+>/<c:\w+>/<i:\d+>/<a:\w+>'  => '<m>/<c>/<a>/index',
        '<m:\w+>/<c:\w+>/<i:\d+>/<a:\w+>'           => '<m>/<c>/<a>/options',

        'PUT,PATCH <m:\w+>/<c:\w+>/<i:\d+>/<a:\w+>/<sub_id:\d+>' => '<m>/<c>/<a>/update',
        'DELETE <m:\w+>/<c:\w+>/<i:\d+>/<a:\w+>/<sub_id:\d+>'    => '<m>/<c>/<a>/delete',
        'GET,HEAD <m:\w+>/<c:\w+>/<i:\d+>/<a:\w+>/<sub_id:\d+>'  => '<m>/<c>/<a>/view',
        '<m:\w+>/<c:\w+>/<i:\d+>/<a:\w+>/<sub_id:\d+>'           => '<m>/<c>/<a>/options',
    ],
];
