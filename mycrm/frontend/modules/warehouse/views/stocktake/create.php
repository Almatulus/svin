<?php


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Stocktake */
/* @var $form \core\forms\warehouse\stocktake\StocktakeCreateForm */


$this->title = Yii::t('app', 'Stocktakes');
$this->params['breadcrumbs'][]    = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 
    'label' => $this->title, 
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');

?>

<?= $this->render('/common/_tabs') ?>

<?= $this->render('_steps', ['model' => $model]) ?>

<div class="column_row">
    <h2><?= Yii::t('app', 'New stocktake'); ?></h2>
</div>

<?= $this->render('_form', [
    'model' => $form,
]) ?>

