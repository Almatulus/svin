<?php

/* @var $this yii\web\View */
/* @var $model core\models\user\User */

$this->title = Yii::t('app', 'Creating User');
$this->params['breadcrumbs'][] =
    ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Users'), 'url' => ['/user/index']];
$this->params['breadcrumbs'][] = "<h1>{$this->title}</h1>";
?>
<div class="user-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
