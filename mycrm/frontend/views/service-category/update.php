<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\ServiceCategory */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Service Category',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Service Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name];
?>
<div class="service-category-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
