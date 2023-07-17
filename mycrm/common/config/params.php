<?php

$params = [
    'adminEmail'             => 'info@inlife.kz',
    'supportEmail'           => 'support@inlife.kz',
    'scheduleInterval'       => 5, // minutes
    'api_host'               => getenv('HOST_API'),
    'crm_host'               => getenv('HOST_CRM'),
    'dev_host'               => getenv('HOST_DEV'),
    'vue_host'               => getenv('HOST_VUE'),
    'calendar_host'          => getenv('HOST_VUE_CALENDAR'),
    'online_widget_host'     => getenv('DOMAIN_WIDGET'),
    'defaultImageId'         => 1,
    'staffDefaultImageId'    => 392,
    'customerDefaultImageId' => 392,
    'divisionDefaultImageId' => 1,
    'debtPaymentId'          => 7,
    'fcm_server_key'         => getenv('FIREBASE_KEY'),
    'sms_cost'               => 8, // tenge
    'admin_phone_number'     => '+7 771 015 15 11',
    'call_phone_number'      => '+7 705 446 22 77',
    // wallet one
    'wallet_one_link'        => 'https://wl.walletone.com/checkout/checkout/Index',
    'wallet_one_id'          => '149701241028',
    'wallet_one_signature'   => '324f757c5a7478595a3667347758556d426343535d5f5b39414d39',
    'app_ios_version'        => getenv('APP_IOS_VERSION'),
    'app_ios_update_url'     => 'itms://itunes.apple.com/us/app/apple-store/id1332260619?mt=8',
    'app_android_version'    => getenv('APP_ANDROID_VERSION'),
    'app_android_update_url' => 'https://mycrm.kz',
];

if (file_exists(__DIR__ . '/params.local.php')) {
    $params = \yii\helpers\ArrayHelper::merge($params, require_once(__DIR__ . '/params.local.php'));
}

return $params;

