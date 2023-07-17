<?php

use core\models\company\Company;
use core\models\ServiceCategory;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\admin\search\CompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Companies');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div><h1>{link}</h1></li>',
    'label'    => $this->title
];
?>
<div class="company-index">

    <?= $this->render('_search', ['model' => $searchModel]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel'  => $searchModel,
        'columns'      => [
            [
                'attribute'      => 'name',
                'contentOptions' => function (Company $company) {
                },
                'format'         => 'html',
                'value'          => function (Company $model) {
                    return Html::a($model->name, ['update', 'id' => $model->id],
                        $model->status ? [] : ['style' => 'color:red']);
                }
            ],
            [
                'attribute' => 'head_name',
                'filter'    => false,
                'value'     => function (Company $model) {
                    return $model->getCeoName();
                }
            ],
            [
                'attribute' => 'category_id',
                'value'     => function (Company $model) {
                    return $model->category->name ?? "";
                }
            ],
            [
                'attribute' => 'tariff_id',
                'value'     => 'tariff.name'
            ],
            [
                    'attribute' => 'balance',
                    'format' => 'html',
                    'value' => function(Company $model){
                        return Html::a(Yii::$app->formatter->asDecimal($model->getBalance()), ['payment', 'id' => $model->id]);
                    }
            ],
            [
                    'attribute' => 'lastTariffPayment.start_date',
                    'format' => 'html',
                    'value' => function (Company $model) {
                        $lastPayment = $model->lastTariffPayment;
                        if ( ! $lastPayment) {
                            return null;
                        }

                        return Html::a(Yii::$app->formatter->asDate($lastPayment->start_date), ['payment-logs', 'id' => $model->id]);
                    }
            ],
            [
                'attribute' => 'lastTariffPayment.period',
                'value'     => 'lastTariffPayment.interval'
            ],
            'lastTariffPayment.sum:currency',
            'lastTariffPayment.nextPaymentDate:date',
            [
                'label'  => 'Дней простоя',
                'format' => 'html',
                'value'  => function (Company $model) {
                    $lastOrder = $model->lastOrder;
                    if ($lastOrder) {
                        $start = new \DateTime();
                        $end = new \DateTime($lastOrder->created_time);
                        $days = $start->diff($end)->days;
                        return Html::tag('span', $days, ['class' => $days > 5 ? 'red' : '']);
                    }
                    return null;
                }
            ],
            [
                'attribute' => 'lastTask.type',
                'value'     => 'lastTask.typeName'
            ],
            'lastTask.due_date:datetime',
            [
                'class'    => 'yii\grid\ActionColumn',
                'buttons'  => [
                    'tasks' => function ($url, Company $model) {
                        return Html::a('<i class="glyphicon glyphicon-tasks"></i>',
                            ['task/index', 'company_id' => $model->id]);
                    },
                    'groupCategories' => function ($url, $model, $key) {
                        return Html::a(Html::tag('span', '', ['class' => "glyphicon glyphicon-wrench"]),
                            ['group-categories', 'id' => $model->id], ['data-title' => 'Сгруппировать категории']);
                    },
                ],
                'template' => '{groupCategories}{tasks}',
                'visibleButtons' => [
                    'groupCategories' => function ($model) {
                        return $model->category_id == ServiceCategory::ROOT_BEAUTY;
                    }
                ]
            ]
        ],
    ]); ?>

</div>
