<?php

use core\helpers\StaffHelper;
use core\models\Staff;
use core\forms\customer\statistic\StatisticStaff;
use kartik\grid\GridView;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\forms\customer\StatisticForm */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app','Statistic').' - '.Yii::t('app','Occupancy');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>', 'label' => Yii::t('app', 'Statistic'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app','Occupancy');
?>
<div class="customer-loyalty-create">

    <?php $form = ActiveForm::begin([
        'action' => ['occupancy'],
        'method' => 'get',
    ]); ?>

    <?= $this->render('_form', [
        'model' => $model,
        'form' => $form,
    ]) ?>

    <div class="col-md-12">
        <div class="pull-left">
            <?php
            $datetimeTo = new DateTime($model->to);
            $datetimeFrom = new DateTime($model->from);
            $difference = $datetimeTo->diff($datetimeFrom)->days + 1;
            echo sprintf('Выбран период длительностью %d дней',$difference);
            ?>
        </div>

        <div class="form-group pull-right">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php
        //TODO This scary gridview could be highly optimized with activequery (in schedule requests)
        $totalWorkingHours = $totalOccupancy = $totalDownTime = $totalWorkedHours = 0;
        $totalWorkingHours = $model->getTotalWorkTime() / 60;
        $totalWorkedHours = $model->getTotalOrderedTime() / 60;
        $totalDownTime = $totalWorkingHours - $totalWorkedHours;
        if ($totalWorkingHours !== 0)
            $totalOccupancy += $totalWorkedHours / $totalWorkingHours * 100;
    ?>

    <div class="col-md-12">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'beforeRow' => function(StatisticStaff $staff) use ($model) {
                $staff->from = $model->from;
                $staff->to = $model->to;
            },
            'showFooter' => true,
            'columns' => [
                [
                    'format' => 'html',
                    'attribute' => 'status',
                    'value' => function ($data) {
                        $class = "label";
                        switch($data->status)
                        {
                            case Staff::STATUS_ENABLED:
                                $class .= " label-success";
                                break;
                            case Staff::STATUS_DISABLED:
                                $class .= " label-danger";
                                break;
                            case Staff::STATUS_FIRED:
                                $class .= " label-danger";
                                break;
                        }
                        return "<span class='{$class}'>" . StaffHelper::getStatuses()[$data->status] . "</span>";
                    }
                ],
                [
                    'format' => 'html',
                    'value' => function (StatisticStaff $staff) {
                        return Html::a(Html::img(\yii\helpers\Url::to(['image/image', 'id' => $staff->image_id, 'size' => 40]),
                            ['alt' => $staff->getFullName()]), ['view', 'id' => $staff->id], ['class' => $staff->color . ' image']);
                    },
                    'contentOptions' => ['class' => 'color avatar']
                ],
                [
                    'format' => 'html',
                    'attribute' => 'name',
                    'label' => Yii::t('app','Staff'),
                    'value' => function(StatisticStaff $staff)
                    {
                        return
                            Html::a($staff->getFullName(), ['staff/update', 'id' => $staff->id]) . Html::tag("br")
                            .   $staff->company_position;
                    },
                    'footer' => Yii::t('app', 'Total'),
                ],
                [
                    'label' => Yii::t('app', 'Working hours'),
                    'value' => function(StatisticStaff $staff) {
                        return number_format($staff->work_time/(60 / Yii::$app->params['scheduleInterval']), 2,'.',' ');
                    },
                    'hAlign' => 'right',
                    'footer' => number_format($totalWorkingHours, 2,'.',' '),
                ],
                [
                    'label' => Yii::t('app', 'Worked hours'),
                    'value' => function(StatisticStaff $staff) {
                        return number_format($staff->ordered_time/(60 / Yii::$app->params['scheduleInterval']), 2,'.',' ');
                    },
                    'hAlign' => 'right',
                    'footer' => number_format($totalWorkedHours, 2,'.',' '),
                ],
                [
                    'label' => Yii::t('app', 'Downtime'),
                    'value' => function(StatisticStaff $staff) {
                        return number_format(($staff->work_time-$staff->ordered_time)/(60 / Yii::$app->params['scheduleInterval']), 2,'.',' ');
                    },
                    'hAlign' => 'right',
                    'footer' => number_format($totalDownTime, 2,'.',' '),
                ],
                [
                    'label' => Yii::t('app', 'Occupancy'),
                    'value' => function(StatisticStaff $staff) {
                        if($staff->work_time != 0)
                            return number_format(($staff->ordered_time/$staff->work_time*100),2,'.',' ').'%';
                        else
                            return '0 %';
                    },
                    'hAlign' => 'right',
                    'footer' => number_format($totalOccupancy, 2,'.',' ').'%',
                ],
            ],
        ]); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
