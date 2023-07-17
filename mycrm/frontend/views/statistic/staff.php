<?php

use core\forms\customer\statistic\StatisticStaff;
use core\helpers\DateHelper;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model core\forms\customer\StatisticStaffForm */

$this->title = Yii::t('app','Statistic').' - '.Yii::t('app','Staff');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label' => Yii::t('app', 'Statistic'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = Yii::t('app','Staff');
$this->params['bodyID']        = 'summary';
?>

<div class="statistic-staff">

    <?= $this->render('forms/_staff', ['model' => $model]); ?>

    <?php
    $totalRevenue = $totalWorkedHours = 0;
    $staffs = $dataProvider->models;
    $totalRevenue = $dataProvider->query->sum('revenue');
    foreach ($staffs as $key => $staff) {
        $staff->formModel = $model;
        $totalWorkedHours += $model->getOrderedTime($staff->id) / 60;
    }
    $order_payments = Yii::$app->user->identity->company->getCostItems()->orderPayment()->all();

    ?>
    <div class="row">
        <div class="col-md-12">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'showFooter' => true,
                'columns' => [
                    [
                        'format' => 'html',
                        'value' => function (StatisticStaff $staff) {
                            $content = Html::img(Url::to(['image/image', 'id' => $staff->image_id, 'size' => 40]), ['alt' => $staff->getFullName()]);
                            return Html::a($content, ['view', 'id' => $staff->id], ['class' => $staff->color . ' image']);
                        },
                        'contentOptions' => ['class' => 'color avatar']
                    ],
                    [
                        'format' => 'html',
                        'label' => Yii::t('app','Staff ID'),
                        'value' => function(StatisticStaff $staff)
                        {
                            return Html::a($staff->getFullName(), ['staff/update', 'id' => $staff->id])
                                    . Html::tag("br")
                                    . implode(\core\models\company\CompanyPosition::STRING_DELIMITER,
                                    ArrayHelper::getColumn($staff->companyPositions, 'name'));
                        },
                        'footer' => Yii::t('app', 'Total'),
                    ],
                    [
                        'label' => Yii::t('app', 'Orders Count Service'),
                        'value' => function (StatisticStaff $staff) use ($model) {
                            $staff->setFormModel($model);
                            return Html::a($staff->getServicesCount(), Url::to([
                                '/order/order/index',
                                'from_date'           => $model->from,
                                'to_date'             => $model->to,
                                'division_id'         => $model->division_id,
                                'division_service_id' => $model->service_id,
                                'staff_id'            => $staff->id,
                                'status'              => \core\helpers\order\OrderConstants::STATUS_FINISHED,
                                'service_categories'  => $model->service_categories
                            ]));
                        },
                        'format' => 'html',
                    ],
                    [
                        'header' => "# товаров",
                        'value' => function (StatisticStaff $staff) use ($model) {
                            $staff->setFormModel($model);
                            return Html::a($staff->getProductsCount(), Url::to([
                                '/warehouse/usage/index',
                                'staff_id' => $staff->id,
                                'start' => $model->from,
                                'end' => $model->to
                            ]));
                        },
                        'format' => 'html'
                    ],
                    [
                        'label'  => Yii::t('app', 'Worked hours'),
                        'value'  => function (StatisticStaff $staff) {
                            $orderedTime = $staff->getOrderedTime();
                            return number_format(($orderedTime / 60), 2,'.',' ');
                        },
                        'footer' => number_format($totalWorkedHours, 2,'.',' '),
                    ],
                    [
                        'header' => Yii::t('app', 'Average amount of time') . " <br> " .
                                    Yii::t('app', 'used per client'),
                        'value'  => function (StatisticStaff $staff) {
                            return DateHelper::convertMinutesToHumanReadableFormat($staff->getAverageOrderedTime());
                        }
                    ],
                    [
                        'attribute' => 'revenue',
                        'label'     => Yii::t('app', 'Price'),
                        'value'     => function(StatisticStaff $staff) use($model){
                            return Yii::$app->formatter->asDecimal($staff->revenue ?: 0);
                        },
                        'footer'    => Yii::$app->formatter->asDecimal($totalRevenue),
                        'format' => 'html',
                    ],
                    [
                        'attribute' => 'total_paid',
                        'label'     => Yii::t('app', 'Paid'),
                        'value'     => function(StatisticStaff $staff) use($model, $order_payments){
                            return Html::a(Yii::$app->formatter->asDecimal($staff->getTotalPaid() ?: 0), Url::to([
                                '/finance/cashflow/index',
                                'sFrom' => $model->from,
                                'sTo' => $model->to,
                                'sDivision' => $model->division_id,
                                'sStaff' => $staff->id,
                                'sCost' => ArrayHelper::getColumn($order_payments, 'id'),
                                'isOrder' => 1
                            ]));
                        },
                        'format' => 'html',
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>
