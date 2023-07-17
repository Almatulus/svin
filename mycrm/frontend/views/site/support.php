<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title                   = Yii::t('app', 'Support');
$this->params['breadcrumbs'][] = "<div class='icon sprite-breadcrumbs_help'></div><h1>{$this->title}</h1>";
?>

<h2><?= Yii::t('app', 'Knowledge Base') ?></h2>
<p>Для того, чтобы сделать пользование системой MyCRM удобной и понятной, мы создали <?= Html::a('Базу Знаний', null) ?>
    , которая содержит подробные инструкции .<br> Удобное окно поиска, позволит вам найти ответы на ваши вопросы
    максимально быстро.</p>
<p><?= Html::a(Yii::t('app', 'Go to the Knowledge Base'), null, ['class' => 'btn btn-primary']) ?></p>
<h2><?= Yii::t('app', 'Contact Form') ?></h2>
<p>Если вы не нашли ответа на свой вопрос в <?= Html::a('Базе знаний', null) ?>, отправьте запрос в нашу команду
    поддержки клиентов.<br> Вся информация о вас, придет к нам автоматически.</p>

<?php $form = ActiveForm::begin(['id' => 'contact-form', 'options' => ['class' => 'simple_form']]); ?>
<ol>
    <li class="control-group text required help_query">
        <div class="controls">
            <?= $form->field($model, 'query')->textarea(['cols' => 40, 'class' => 'string options']) ?>
        </div>
    </li>
    <li class="control-group email required help_email">
        <div class="controls">
            <?= $form->field($model, 'email'); ?>
        </div>
    </li>
    <li class="control-group file optional help_attachment">
        <div class="controls">
            <?= $form->field($model, 'attachment')->fileInput(); ?>
        </div>
    </li>
</ol>
<div class="form-actions">
    <button id='help-btn' class="btn btn-primary" type="submit"><?= Yii::t('app', 'Submit Query'); ?></button>
</div>
<?php $form->end(); ?>

<h2><?= Yii::t('app', 'Contact us by phone') ?></h2>

<p>
Вы можете получить поддержку, позвонив по телефону <strong>+7 727 220 74 77</strong><br>
Мы работаем круглосуточно и без выходных.<br>
</p>

<p><?= Html::a(Yii::t('app', 'Download AnyDesk'), 'https://anydesk.com/ru/download', ['class' => 'btn btn-primary', 'target' => '_blank']) ?></p>
