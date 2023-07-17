<?php


/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Delivery */
/* @var $products \core\models\warehouse\DeliveryProduct[] */

$this->title = Yii::t('app', 'Delivery');
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Deliveries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>

<?= $this->render('/common/_tabs') ?>

<div class="delivery-create">

    <?= $this->render('_form', [
        'model' => $model,
        'products' => $products
    ]) ?>

</div>
