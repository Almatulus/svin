<?php

namespace yii\web;

use yii\base\Module;

die('Если вышла эта ошибка, удалите autocomplete\Application.php');

/**
 * Class Application
 * @package yii\web
 *
 * @property \core\services\PushService $pushService
 * @property \core\services\user\Logger $userLogger
 * @property \core\services\customer\LoyaltyManager $loyaltyManager
 */
abstract class Application extends Module {

    /** @var \common\components\parsers\ServiceParser */
    public $serviceParser;

    /** @var \common\components\parsers\ProductParser */
    public $productParser;

    /** @var \common\components\excel\Excel*/
    public $excel;

    /** @var \common\components\SMSC */
    public $sms;

    /** @var \yii\queue\Queue */
    public $queue;
}