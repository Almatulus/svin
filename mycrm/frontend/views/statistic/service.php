<?php

use core\forms\customer\statistic\StatisticService;
use core\helpers\HtmlHelper as Html;
use core\helpers\order\OrderConstants;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $model core\forms\customer\StatisticForm */

$this->title = Yii::t('app', 'Statistic') . ' - ' . Yii::t('app', 'Services');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label' => Yii::t('app', 'Statistic'),
    'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Services');

$textAlign = 'text-right';
?>
<div class="customer-loyalty-create">

    <?php $form = ActiveForm::begin([
        'action' => ['service'],
        'fieldConfig' => ['template' => "{input}\n{hint}\n{error}"],
        'method' => 'get',
    ]); ?>

    <?= $this->render('forms/_service', [
        'model' => $model,
        'form' => $form,
    ]) ?>

    <div class="col-md-12 column_row">
        <div class="pull-left">
            <?php
            $datetimeTo = new DateTime($model->to);
            $datetimeFrom = new DateTime($model->from);
            $difference = $datetimeTo->diff($datetimeFrom)->days;
            echo sprintf('Выбран период длительностью %d суток', $difference);
            ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <?php
    //TODO This scary gridview could be highly optimized with activequery (in schedule requests)
    $totalRevenue = $dataProvider->query->sum('revenue');
    $totalOrdersCount = $dataProvider->query->sum('orders_count');
    $totalAverageCost = $totalOrdersCount == 0 ? 0 : $totalRevenue / $totalOrdersCount;
    $interval = Yii::$app->params['scheduleInterval'];
    ?>

    <div class="col-md-12">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'beforeRow' => function (StatisticService $service) use ($model) {
                $service->from = $model->from;
                $service->to = $model->to;
            },
            'summary' => Html::getSummary(),
            'showFooter' => true,
            'columns' => [
                [
                    'attribute' => 'service_name',
                    'value' => function(StatisticService $model){
                        return Html::a($model['service_name'], \yii\helpers\Url::to([
                            '/division/service/update',
                            'id' => $model['id']
                        ]));
                    },
                    'format' => 'html',
                    'footer' => Yii::t('app', 'Total'),
                ],
                [
                    'attribute' => 'orders_count',
                    'value' => function(StatisticService $model){
                        return Html::a($model->orders_count, Url::to([
                            '/order/order/index',
                            'from_date' => $model->from,
                            'to_date' => $model->to,
                            'division_id' => $model->division,
                            'division_service_id' => $model['id'],
                            'status' => OrderConstants::STATUS_FINISHED
                        ]));
                    },
                    'label' => Yii::t('app', 'Orders Count Service'),
                    'hAlign' => 'right',
                    'footer' => Html::a(Yii::$app->formatter->asDecimal($totalOrdersCount), Url::to([
                        '/order/order/index',
                        'from_date' => $model->from,
                        'to_date' => $model->to,
                        'division_id' => $model->division,
                        'status' => OrderConstants::STATUS_FINISHED
                    ])),
                    'format' => 'html',
                ],
                [
                    'attribute' => 'revenue',
                    'value' => function(StatisticService $model){
                        return Html::a(Yii::$app->formatter->asDecimal($model->revenue), [Url::to([
                            '/finance/cashflow/index',
                            'sFrom' => $model->from,
                            'sTo' => $model->to,
                            'sDivision' => $model->division,
                            'sDivisionService' => $model['id'],
                            'sCost' => -1
                        ])]);
                    },
                    'format' => 'html',
                    'label' => Yii::t('app', 'Revenue'),
                    'hAlign' => 'right',
                    'footer' => Html::a(Yii::$app->formatter->asDecimal($totalRevenue), Url::to([
                        '/finance/cashflow/index',
                        'sFrom' => $model->from,
                        'sTo' => $model->to,
                        'sDivision' => $model->division,
                        'sCost' => -1
                    ])),
                ],
                [
                    'attribute' => 'average_cost',
                    'format' => 'decimal',
                    'label' => Yii::t('app', 'Average Cost'),
                    'hAlign' => 'right',
                    'value' => function(StatisticService $model) {
                        // P.S так как отображение прямиком из SQL не учитывает дробные (ex: 9 166.00 ₸ вместо 9 166.67 ₸)
                        return $model->revenue / $model->orders_count;
                    },
                    'footer' => Yii::$app->formatter->asDecimal($totalAverageCost),
                ],
                [
                    'attribute' => 'revenue',
                    'label'     => Yii::t('app', '% from Total Revenue'),
                    'value'     => function (StatisticService $service) use ($totalRevenue) {
                        if ($totalRevenue != 0)
                            return Yii::$app->formatter->asPercent($service->revenue / $totalRevenue);
                        else
                            return '0 %';
                    },
                    'hAlign'    => 'right',
                ],
            ],
        ]); ?>
    </div>

</div>
