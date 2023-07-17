<?php

/* @var $this yii\web\View */
/* @var $model \core\forms\finance\CostItemUpdateForm */

$this->title = Yii::t('app', 'Updating {something}', ['something' => $model->costItem->getFullName()]);
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-sign-out-alt"></i> {link}</li>',
    'label' => Yii::t('app', 'Cost Items'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $model->costItem->getFullName();
?>
<div class="company-cost-item-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
