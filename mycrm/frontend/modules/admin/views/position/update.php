<?php

/* @var $this yii\web\View */
/* @var $model \core\models\Position */
/* @var $documentFormList array */

$this->title = Yii::t('app', 'Updating {something}', ['something' => $model->name]);
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Company positions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name];
?>
<div class="company-position-update">

    <?= $this->render('_form', [
        'model' => $model,
        'documentFormList' => $documentFormList,
    ]) ?>

</div>
