<?php

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Delivery */

$this->title = Yii::t('app', 'Delivery') . ' #' .$model->id;
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Deliveries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;

?>

<?= $this->render('/common/_tabs') ?>

<div class="delivery-update">

    <?= $this->render('_form', [
        'model' => $model,
        'products' => $products
    ]) ?>

</div>
