<?php
namespace core\helpers\color;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ColorSelect2Asset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/color-select2.css',
    ];
    public $js = [
        'js/color-select2.js',
    ];
    public $jsOptions = ['position' => View::POS_HEAD];
    public $depends = [
        // 'frontend\assets\AppAsset',
    ];

    public function init() {

        parent::init();
    }
}
