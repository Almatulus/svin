<?php


/* @var $this yii\web\View */

$this->title                   = Yii::t('app', 'Create Customer');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Customers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
