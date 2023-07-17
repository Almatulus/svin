<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,600,700&subset=latin,latin-ext',
        'https://fonts.googleapis.com/css?family=Lato:300&subset=latin,latin-ext',
        'build/css/all.min.css',
    ];
    public $js = [
        'build/js/vendor.min.js',
        'build/js/all.min.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}
