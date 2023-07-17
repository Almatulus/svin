<?php

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCostItem */

$this->title                   = Yii::t('app', 'Create');
$this->params['breadcrumbs'][] = ['template' => '<li><i class="fa fa-sign-out-alt"></i> {link}</li>', 'label' => Yii::t('app', 'Cost Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-cost-item-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
