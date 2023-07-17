<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\customer\search\SubscriptionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Season tickets');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 
    'label' => Yii::t('app', 'Customers'), 
    'url' => ['/customer/customer/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-subscription-index">

    <?= $this->render('_search', ['model' => $searchModel]); ?>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'key',
            [
                'attribute' => 'statusLabel',
                'label' => Yii::t('app', 'Status')
            ],
            [
                'attribute' => 'typeLabel',
                'label' => Yii::t('app', 'Type')
            ],
            [
                'attribute' => 'price',
                'format' => 'decimal'
            ],
            'start_date',
            'first_visit',
            [
                'attribute' => 'companyCustomer.customer.fullName',
                'label' => Yii::t('app', 'Customer')
            ],
            'companyCustomer.customer.phone',
            'end_date',
            [
                'attribute' => 'number_of_persons',
                'header' => 'Количество<br>человек'
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}'
            ],
        ],
    ]); ?>
</div>
