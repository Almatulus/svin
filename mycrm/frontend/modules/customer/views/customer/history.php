<?php

use core\helpers\HtmlHelper as Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\order\search\OrderSearch */
/* @var $model \core\models\customer\CompanyCustomer */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Order History');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Customers'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = [
    'label'    => $model->customer->getFullName(),
    'url'      => ['view', 'id' => $model->id]
];
$this->params['breadcrumbs'][] = $this->title;
$this->params['bodyID']        = 'customer';

?>
<div class="customer-history">

    <?= GridView::widget([
        'dataProvider'   => $dataProvider,
        'columns'        => [
            [
                'label'          => Yii::t('app', 'Session'),
                'attribute'      => 'datetime',
                'format'         => ['datetime'],
                'contentOptions' => ['class' => 'nowrap'],
            ],
            [
                'attribute' => 'staff.fullName',
                'label'     => Yii::t('app', 'Staff ID')
            ],
            [
                'attribute' => 'price',
                'format'    => 'decimal'
            ],
        ],
        'striped'        => true,
        'responsive'     => true,
        'responsiveWrap' => false,
        'summary'        => Html::getSummary(),
    ]) ?>

</div>