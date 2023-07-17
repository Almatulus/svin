<?php
/* @var $this \yii\web\View */
/* @var $content string */
use frontend\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);

$options = [];
if (isset($this->params['bodyID'])) {
    $options['id'] = $this->params['bodyID'];
}
if (isset($this->params['bodyClass'])) {
    $options['class'] = $this->params['bodyClass'];
}
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <?php if (YII_ENV_PROD) : ?>
            <?= $this->render('statistics.php') ?>
        <?php endif; ?>
        <script>
            var api_host = '<?= getenv('HOST_API') ?>';
            var user_token = '<?= Yii::$app->user->identity->getValidAccessToken() ?>';
            var division_notification = <?= json_encode(Yii::$app->user->identity->company->getDivisionsDefaultNotificationTimeList())?>;
        </script>
    </head>
    <?= Html::beginTag('body', $options); ?>
    <input id="myid" type="hidden" value="<?= Yii::$app->user ? Yii::$app->user->identity->id : '' ?>">
    <?php $this->beginBody() ?>
    <?= $this->render('header.php') ?>
    <div class="main-container" id="main-container">
        <?= $this->render('sidebar.php') ?>
        <?= $this->render('content.php', ['content' => $content]) ?>
    </div>
    <div id="fullscreen" style="display: none">
        <img src="" alt="">
    </div>
    <div class="loading_indicator" id="loading_indicator">
        <div class="spinner-icon"></div>
        загрузка ...
    </div>
    <?php if (YII_ENV_PROD): ?>
        <script type="text/javascript">
            var _smartsupp = _smartsupp || {};
            _smartsupp.key = '9c0ecb5e0b7a66e5258c2ab095fff2c7ff84e193';
            window.smartsupp || (function (d) {
                var s, c, o = smartsupp = function () {
                    o._.push(arguments)
                };
                o._ = [];
                s = d.getElementsByTagName('script')[0];
                c = d.createElement('script');
                c.type = 'text/javascript';
                c.charset = 'utf-8';
                c.async = true;
                c.src = '//www.smartsuppchat.com/loader.js?';
                s.parentNode.insertBefore(c, s);
            })(document);
        </script>
    <?php endif; ?>
    <?php $this->endBody() ?>
    <div class="notifications">
    </div>
    <audio id="sound" src="<?= Yii::getAlias('@web') ?>/audio/tone.wav" preload></audio>
    <script src="https://crm.mycrm.kz/socket.io/socket.io.js" async></script>
    <?= Html::endTag('body'); ?>
    </html>
<?php $this->endPage() ?>