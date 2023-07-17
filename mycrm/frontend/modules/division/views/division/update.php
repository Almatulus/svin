<?php

/* @var $this yii\web\View */
/* @var $model core\models\division\Division */
/* @var $divisionPhones core\models\division\DivisionPhone[] */

$this->title                   = Yii::t('app', 'Updating {something}', ['something' => $model->name]);
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => $model->division->company->name,
    'url' => ['/company/default/update', 'id' => $model->company_id]
];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="division-update">
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
