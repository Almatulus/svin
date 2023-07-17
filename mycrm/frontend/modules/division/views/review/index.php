<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\search\DivisionReviewSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Division Reviews');
$this->params['breadcrumbs'][]    = ['template' => '<li class="active"><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => $this->title];
?>
<div class="division-review-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'format' => 'html',
                'attribute' => 'division_id',
                'value' => function($data)
                {
                    return $data->division->name . " (" . $data->division->address . ")";
                }
            ],
            [
                'attribute'=>'customer_id',
                'format' => 'html',
                'value' => function($data){
                    $customer = $data->customer;
                    $info = [
                        $customer->name,
                        $customer->phone,
                    ];
                    return implode(",<br>", $info);
                }
            ],
            [
                'attribute' => 'created_time',
                'value' => function($data)
                {
                    $date = new DateTime($data->created_time);
                    return $date->format("d.m, H:i");
                }
            ],
            'value',
            'comment:ntext',
        ],
    ]); ?>

</div>
