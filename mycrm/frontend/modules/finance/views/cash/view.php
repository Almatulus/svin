<?php

use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCash */

$this->title = $model->name;
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-credit-card"></i> {link}</li>',
    'label'    => Yii::t('app', 'Cashes'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

$cashes = ArrayHelper::map(
    \core\models\finance\CompanyCash::find()->company()->active()->andWhere(['<>', 'id', $model->id])->all(),
    'id',
    'name'
);
?>

<div class="company-cash-view">

    <?= $this->render('_cash_report', [
        'model'           => $model,
        'cashes'          => $cashes
    ]) ?>
</div>

<?php
Modal::begin([
    "id"      => "cash-modal",
    'header'  => $model->name,
    'options' => ['tabindex' => '', 'style' => 'overflow-y: auto;'],
]);
echo $this->render('_update_form', ['model' => $model]);
Modal::end();


Modal::begin([
    "id"      => "cash-transfer-modal",
    'header'  => "Перевод денег",
    'options' => ['tabindex' => '', 'style' => 'overflow-y: auto;'],
]);
echo $this->render('_transfer_form', [
    'modelForm' => new \core\forms\finance\CashTransferForm(),
    'model'     => $model,
    'cashes'    => $cashes
]);
Modal::end();
?>
