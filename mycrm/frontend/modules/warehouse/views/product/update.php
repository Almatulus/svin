<?php

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Product */

$this->title = $model->name;
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;

?>

<?= $this->render('/common/_tabs') ?>

<div class="product-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
