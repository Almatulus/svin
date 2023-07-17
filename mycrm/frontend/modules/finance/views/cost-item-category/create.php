<?php

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCostItemCategory */

$this->title = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-sign-out-alt"></i> {link}</li>',
    'label'    => Yii::t('app', 'CostItemsCategory'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-cost-item-category-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
