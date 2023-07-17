<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\document\DocumentForm */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
        'modelClass' => 'Document Form',
    ]) . $model->name;
$this->params['breadcrumbs'][] = [
    'template' => '<li><span class="fa fa-image"></span> {link}</li>',
    'label'    => Yii::t('app', 'Document Forms'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="document-form-update">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
