<?php

use core\models\company\Company;
use core\models\division\Division;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $schedules array */
/* @var $staffs \core\models\Staff[] */
/* @var $model \core\forms\ScheduleFilterForm */

$this->title = Yii::t('app', 'Time Schedule');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $this->title
];
$this->params['bodyID'] = 'schedule';
?>
<div class="staff-schedule">
    <div class="staff-schedule-filter">
        <?php $form = ActiveForm::begin([
            'method'      => 'get',
            "fieldConfig" => [
                "template" => "{input}{error}"
            ]
        ]); ?>

        <div class="row">
            <div class="col-sm-3">
                <?= $form->field($model, 'start_date')->widget(DatePicker::className(), [
                    'type'          => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format'    => 'yyyy-mm-dd'
                    ]
                ]) ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'end_date')->widget(DatePicker::className(), [
                    'type'          => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format'    => 'yyyy-mm-dd'
                    ]
                ]) ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'division_id')->widget(Select2::className(), [
                    'data'          => Division::getOwnDivisionsNameList(),
                    'options'       => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Divisions')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'language'   => 'ru'
                    ],
                    'size'          => 'sm',
                    'showToggleAll' => false,
                ]) ?>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
    <div class="staff-schedule-wrapper">
        <table id="staff-schedule-table" cellspacing="0" cellpadding="0">
            <thead>
            <tr>
                <th>Сотрудник</th>
                <?php $date_iterator = new DateTime($model->getStartDate()->format("Y-m-d")); ?>
                <?php while ($date_iterator <= $model->getEndDate()): ?>
                    <td>
                        <?= $date_iterator->format("d") ?>
                        <br><?= Yii::t('app', $date_iterator->format("M")) ?>
                        <br>
                        <small>(<?= Yii::t('app', $date_iterator->format('D')) ?>)</small>
                    </td>
                    <?php $date_iterator->modify("+1 day") ?>
                <?php endwhile; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($staffs as $staff): ?>
                <?php foreach ($divisions as $division): ?>
                    <?php if (!isset($schedules[$staff->id][$division->id])) continue; ?>
                    <tr>
                        <th>
                            <?= $staff->getFullName() ?>
                            <?php if (count($divisions) > 1): ?>
                                <br/>
                                (<?= $division->name ?>)
                            <?php endif; ?>
                        </th>
                        <?php $date_iterator = new DateTime($model->getStartDate()->format("Y-m-d")); ?>
                        <?php while ($date_iterator <= $model->getEndDate()): ?>
                            <?php
                            $date = $date_iterator->format('Y-m-d');
                            $has_schedule = $schedules[$staff->id][$division->id][$date] != null;
                            $title = "";
                            $break_start = null;
                            $break_end = null;
                            if ($has_schedule) {
                                $title = $schedules[$staff->id][$division->id][$date]->getScheduleTitle();
                                $start_time = $schedules[$staff->id][$division->id][$date]->start_at;
                                $end_time = $schedules[$staff->id][$division->id][$date]->end_at;
                                $break_start = $schedules[$staff->id][$division->id][$date]->break_start;
                                $break_end = $schedules[$staff->id][$division->id][$date]->break_end;
                            } else {
                                $start_time = $division->working_start;
                                $end_time = $division->working_finish;
                            }
                            $end_time = Yii::$app->formatter->asTime($end_time);
                            ?>
                            <?= Html::tag('td',
                                '<span class="workdate-title">' . $title . '</span><div class="spinner"></div>', [
                                    'class'            => "workdate " . ($has_schedule ? "has_schedule" : ""),
                                    'data-staff'       => $staff->id,
                                    'data-division'    => $division->id,
                                    'data-date'        => $date_iterator->format('Y-m-d'),
                                    'data-start'       => Yii::$app->formatter->asTime($start_time),
                                    'data-end'         => $end_time == '00:00' ? '24:00' : $end_time,
                                    'data-break_start' => $break_start ? Yii::$app->formatter->asTime($break_start) : null,
                                    'data-break_end'   => $break_end ? Yii::$app->formatter->asTime($break_end) : null,
                                ]);
                            ?>
                            <?php $date_iterator->modify("+1 day") ?>
                        <?php endwhile; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    Modal::begin([
        'id'      => 'schedule-modal',
        'options' => ['style' => 'z-index: 1000003;'],
        'header'  => '<h4 class="modal-title">' . Yii::t('app', 'Schedule') . '</h4>',
        "footer"  => Html::button(Yii::t('app', 'Save'), ['class' => 'btn btn-primary pull-left btn-save'])
            . Html::button(Yii::t('app', 'Delete'), ['class' => 'btn btn-default pull-left btn-delete hidden'])
    ]);

    /* @var Company $company */
    $company = Yii::$app->user->identity->company;
    $rangeHours = $company->getTimeRangeHours();
    $interval = Yii::$app->params['scheduleInterval'];
    $rangeMinutes = [];
    for ($i = 0; $i < 60; $i += $interval) {
        $minute = ($i / 10 >= 1) ? $i : '0' . $i;
        $rangeMinutes[$minute] = $minute;
    }
    ?>

    <div class="control-group">
        <label class="control-label">
            <?= Yii::t('app', 'Working Hours') ?>
        </label>
        <div class="controls">
            <?= Html::dropDownList('start_hour', null, $rangeHours); ?>
            <?= Html::dropDownList('start_minute', null, $rangeMinutes); ?>

            <span style="margin-left: 5px; margin-right: 5px;">:</span>

            <?= Html::dropDownList('end_hour', null, $rangeHours); ?>
            <?= Html::dropDownList('end_minute', null, $rangeMinutes); ?>
        </div>
    </div>


    <div class="control-group" style="margin-top: 10px;">
        <label class="control-label">
            <?= Yii::t('app', 'Break') ?>
        </label>
        <div class="controls">
            <?= Html::dropDownList('break_start_hour', null, $rangeHours, ['prompt' => '']); ?>
            <?= Html::dropDownList('break_start_minute', null, $rangeMinutes, ['prompt' => '']); ?>

            <span style="margin-left: 5px; margin-right: 5px;">:</span>

            <?= Html::dropDownList('break_end_hour', null, $rangeHours, ['prompt' => '']); ?>
            <?= Html::dropDownList('break_end_minute', null, $rangeMinutes, ['prompt' => '']); ?>
        </div>
    </div>

    <?php Modal::end(); ?>
</div>
