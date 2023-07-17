<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class WidgetAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'source/css/widget.css',
    ];
    public $js = [
        'source/js/testWidget.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}
