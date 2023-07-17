<?php


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Product */

$this->title = Yii::t('app', 'Products');
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>

<?= $this->render('/common/_tabs') ?>

<div class="product-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
