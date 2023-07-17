<?php

namespace frontend\modules\division;

use yii\web\AssetBundle;

class DivisionAssets extends AssetBundle
{
    public $sourcePath = '@app/modules/division/assets';

    public $css = [
    ];

    public $js = [
        'service.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}