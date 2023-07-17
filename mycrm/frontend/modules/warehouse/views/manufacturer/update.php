<?php


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Manufacturer */

$this->title = $model->name;
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Manufacturers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;
?>

<?= $this->render('/common/_tabs') ?>

<div class="manufacturer-update">

    <?= $this->render('_form', [
        'model' => $model
    ]) ?>

</div>
