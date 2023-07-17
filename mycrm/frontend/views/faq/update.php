<?php

/* @var $this yii\web\View */
/* @var $model core\models\FaqItem */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'FAQ',
]) . $model->id;

$this->params['breadcrumbs'][] = [
    'template' => '<li><span class="fa fa-question-circle"></span> {link}</li>',
    'label' => Yii::t('app', 'FAQ'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="faq-item-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
