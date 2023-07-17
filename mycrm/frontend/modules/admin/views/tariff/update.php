<?php

/* @var $this yii\web\View */
/* @var $model core\models\company\Tariff */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
        'modelClass' => Yii::t('app', 'Tariff'),
    ]) . $model->name;
$this->params['breadcrumbs'][] = [
    'template' => '<li>{link}</li>',
    'label'    => Yii::t('app', 'Tariffs'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="tariff-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
