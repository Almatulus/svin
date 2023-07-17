<?php
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<div class="navbar navbar-default navbar-static-top" id="navbar">
    <div class="pull-left">
        <a class="menu-toggler navbar-toggle" id="menu-toggler">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
        <div class="brand">
            <a href="<?= Url::home() ?>" title="MyCRM">
            </a></div>
    </div>
    <div class="pull-right">
        <ul class="navbar-right simple-list">
            <a href="<?= Url::to(['/site/faq'])?>" class="help">
                <span><?= Yii::t('app', 'FAQ') ?></span>
            </a>
            <a href="<?= Url::to(['/site/support'])?>" class="help">
                <span><?= Yii::t('app', 'Support') ?></span>
            </a>
            <a href="<?= Url::to(['/auth/logout'])?>" data-method="post" class="logout"><i
                    class="fa fa-sign-out-alt"></i>Выйти из системы</a>
        </ul>
    </div>
</div>
