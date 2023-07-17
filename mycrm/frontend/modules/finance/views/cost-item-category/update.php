<?php

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCostItemCategory */

$this->title = Yii::t(
    'app',
    'Updating {something}',
    ['something' => $model->name]
);
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-sign-out-alt"></i> {link}</li>',
    'label'    => Yii::t('app', 'CostItemsCategory'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="company-cost-item-category-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
