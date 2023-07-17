<?php


/* @var $this yii\web\View */
/* @var $model core\models\finance\Payroll */
/* @var $services core\models\finance\PayrollService[] */
/* @var $staffs core\models\finance\PayrollStaff[] */

$this->title = Yii::t('app', 'Create Payroll Scheme');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::t('app', 'Payroll Schemes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="payroll-scheme-create">
    <?= $this->render('_form', [
        'model' => $model,
        'services' => $services,
        'staffs' => $staffs,
    ]) ?>
</div>
