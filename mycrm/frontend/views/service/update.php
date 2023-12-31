<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\Service */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Service',
]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Services'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="service-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
