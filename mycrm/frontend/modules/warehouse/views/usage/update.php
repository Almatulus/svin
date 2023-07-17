<?php

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Usage */

$this->title = Yii::t('app', 'Usage') . ' #' .$model->id;
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_stock"></div>{link}</li>', 'label' => Yii::t('app', 'Usage history'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;

?>

<?= $this->render('/common/_tabs') ?>

<div class="usage-update">

    <?= $this->render('_form', [
        'model' => $model,
        'usageProducts' => $usageProducts,
        'disabled' => true
    ]) ?>

</div>
