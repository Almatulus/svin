<?php

use core\models\StaffReview;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\search\StaffReviewSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = Yii::t('app', 'Staff Reviews');
$this->params['breadcrumbs'][] = [
    'template' => '<li class="active"><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $this->title
];
?>
<div class="staff-review-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => [

            [
                'format'    => 'html',
                'attribute' => 'staff_id',
                'value'     => function (StaffReview $model) {
                    return $model->staff->getFullName();
                }
            ],
            [
                'attribute' => 'customer_id',
                'format'    => 'html',
                'value'     => function (StaffReview $model) {
                    $customer = $model->customer;

                    return implode(",<br>", [
                        $customer->name,
                        $customer->phone,
                    ]);
                }
            ],
            [
                'attribute' => 'created_time',
                'format'    => 'datetime',
            ],
            'value',
            'comment:ntext',
        ],
    ]); ?>
</div>
