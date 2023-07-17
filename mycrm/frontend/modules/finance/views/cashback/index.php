<?php

use core\models\company\Cashback;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\finance\search\CashbackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Cashback');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cashback-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions'   => function (Cashback $model, $key, $index, $grid) {
            if ($model->isOutcome()) {
                return ['class' => 'danger'];
            }
        },
        'columns'      => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'company_customer_id',
                'value'     => 'companyCustomer.customer.fullName'
            ],
            [
                'attribute' => 'type',
                'value'     => 'typeName'
            ],
            'amount:currency',
//            'percent',
            // 'status',
            // 'created_by',
            // 'updated_by',
            'created_at',
            // 'updated_at',
        ],
    ]); ?>
</div>
