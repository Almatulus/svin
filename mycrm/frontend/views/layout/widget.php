<?php
use yii\helpers\Html;
use yii\helpers\Url;
use frontend\assets\WidgetAsset;

WidgetAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <link rel="shortcut icon" href="/mycrmicon.ico" type="image/x-icon">
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <?php $this->head() ?>
    </head>
    <?= Html::beginTag('body'); ?>
    <?php $this->beginBody() ?>
    <div class="navbar navbar-default navbar-static-top" id="navbar">
        <div class="brand">
            <img width="150" src="/image/logo.png">
        </div>
    </div>
    <div class="main-container" id="main-container">
        <?= $content ?>
    </div>
    <?php $this->endBody() ?>
    <?= Html::endTag('body'); ?>
    </html>
<?php $this->endPage() ?>