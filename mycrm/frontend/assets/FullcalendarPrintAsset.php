<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class FullcalendarPrintAsset extends AssetBundle
{
    public $sourcePath = '@bower/fullcalendar/dist';
    public $css = [
        'fullcalendar.min.css',
        'fullcalendar.print.min.css',
    ];
    public $cssOptions = [
        'media' => "print"
    ];
    public $depends = [
        'frontend\assets\AppAsset',
    ];
}
