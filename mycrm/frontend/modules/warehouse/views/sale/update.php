<?php

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Sale */

$this->title = Yii::t('app', 'Sale') . ' #' . $model->sale->id;
$this->params['breadcrumbs'][]    = [
    'template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>',
    'label' => Yii::t('app', 'Sales'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $model->sale->id;

?>

<?= $this->render('/common/_tabs') ?>

<div class="sale-update">

    <?= $this->render('_form', [
        'model' => $model,
        'saleProducts' => $saleProducts
    ]) ?>

</div>
