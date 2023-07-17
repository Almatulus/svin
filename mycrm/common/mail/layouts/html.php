<?php
use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body style="margin: 0; padding: 0;">
    <?php $this->beginBody() ?>
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc;">
        <tr>
            <td align="center" bgcolor="#FFF" style="border: 30px solid #76C4F2;">
                <img src="http://crm.reone.info/image/reone_logo.gif" alt="Reone logo" height="150" style="display: block;" />
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                <?= $content ?>
            </td>
        </tr>
        <tr>
            <td bgcolor="#ee4c50" style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px; padding: 30px 30px 30px 30px;">
                &copy; Reone inc, <?= (new DateTime())->format("Y")?><br/>
                Вы можете <a href="<?= \yii\helpers\Url::to("/user/unsubscribe", ['key' => md5("string")]) ?>" style="color: #ffffff;"><font color="#ffffff">отписаться</font></a> от почтовой рассылки в любое время.
            </td>
        </tr>
    </table>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
