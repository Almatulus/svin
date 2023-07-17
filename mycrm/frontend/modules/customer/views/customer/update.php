<?php

/* @var $this yii\web\View */
/* @var $model \core\forms\customer\CompanyCustomerUpdateForm */

$this->title = Yii::t('app', 'Edit');

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Customers'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = [
    'label' => $model->companyCustomer->customer->getFullName(),
    'url'   => [
        'view',
        'id' => $model->companyCustomer->id
    ]
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
