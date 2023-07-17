<?php


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Sale */
/* @var $saleProducts core\models\warehouse\SaleProduct[] */

$this->title = Yii::t('app', 'Sales');
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Sales'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>

<?= $this->render('/common/_tabs') ?>

<div class="sale-create">

    <?= $this->render('_form', [
        'model' => $model,
        'saleProducts' => $saleProducts
    ]) ?>

</div>
