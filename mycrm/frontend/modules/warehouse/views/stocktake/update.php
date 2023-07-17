<?php

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Stocktake */

$this->title = $model->name;
$this->title = Yii::t('app', 'Stocktakes');
$this->params['breadcrumbs'][]    = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 
    'label' => $this->title, 
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>

<?= $this->render('/common/_tabs') ?>

<?= $this->render('_steps') ?>

<div class="column_row">
    <h2><?= Yii::t('app', 'Edit stocktake'); ?></h2>
</div>

<?= $this->render('_form', [
    'model' => $model,
]) ?>
