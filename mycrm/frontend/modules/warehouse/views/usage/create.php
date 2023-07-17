<?php


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Usage */
/* @var $saleProducts core\models\warehouse\UsageProduct[] */

$this->title = Yii::t('app', 'Usage');
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Usage'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>

<?= $this->render('/common/_tabs') ?>

<div class="usage-create">

    <?= $this->render('_form', [
        'model' => $model,
        'usageProducts' => $usageProducts,
        'disabled' => false
    ]) ?>

</div>
