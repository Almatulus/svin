<?php
/* @var $model core\models\order\Order */
/* @var $this yii\web\View */
/* @var $company core\models\Company */

use core\helpers\order\OrderConstants;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Create Order {type}', [
    'type' => Yii::t('app', OrderConstants::getTypes()[$model->type])
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-create">

    <?php $form = ActiveForm::begin(['id' => 'order-form']); ?>

    <?= $this->render('_order_form', [
        'model' => $model,
        'form' => $form
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
