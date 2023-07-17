<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model core\models\finance\CompanyCashflow */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cashflows'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-cashflow-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'date',
            'cost_item_id',
            'cash_id',
            'receiver_mode',
            'contractor_id',
            'customer_id',
            'staff_id',
            'value',
            'comment:ntext',
            'user_id',
            'company_id',
        ],
    ]) ?>

</div>
