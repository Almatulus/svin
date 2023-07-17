<?php

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCashflow */

$this->title                   = $model->date;
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::t('app', 'Company Cashflows'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-cashflow-update">

    <?= $this->render('_form', [
        'model' => $model,
        'type' => $model->cashflow->costItem->type,  // FIXME: Если нужно отобразить статьи всех типов то передаем NULL
    ]) ?>

</div>
