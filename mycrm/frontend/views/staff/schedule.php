<?php

use core\helpers\ScheduleTemplateHelper;
use yii\helpers\Html;

/** @var $this \yii\web\View */
/** @var $model \core\forms\staff\ScheduleTemplateForm */
/** @var $staff \core\models\Staff */

$this->title = Yii::t('app', 'Schedule');
$this->params['breadcrumbs'][] = [
    'template' => '<li class="active"><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => Yii::t('app', 'Staff'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = ['label' => $staff->getFullName(), 'url' => ['view', 'id' => $staff->id]];
$this->params['breadcrumbs'][] = ['label' => $this->title];

$defaultStart = Yii::$app->user->identity->company->getMinWorkingTime();
$defaultEnd = Yii::$app->user->identity->company->getMaxWorkingTime();

$form = \yii\widgets\ActiveForm::begin();
?>
<div class="row">
    <div class="col-sm-3">
        <?= $form->field($model, 'division_id')->dropDownList(\yii\helpers\ArrayHelper::map($staff->divisions, 'id',
            'name')) ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'type')->dropDownList(ScheduleTemplateHelper::types()) ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'start')->widget(\kartik\date\DatePicker::class, [
            'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd']
        ]);
        ?>
    </div>
    <div class="col-sm-3">
        <?= $form->field($model, 'interval_type')->dropDownList(ScheduleTemplateHelper::periods()) ?>
    </div>
</div>

<?= $form->errorSummary($model); ?>

<div class="schedule-intervals">
    <?php $intervalLabels = ScheduleTemplateHelper::intervals($model->type) ?>
    <?php for ($i = 1; $i <= 7; $i++) { ?>
        <?php
        if ($model->template) {
            $model->intervals[$i]['is_enabled'] = isset($model->intervals[$i]);
        } else {
            $model->intervals[$i]['is_enabled'] = $i < 6 ? true : false;
        }
        $model->intervals[$i]['start'] = $model->intervals[$i]['start'] ?? $defaultStart;
        $model->intervals[$i]['end'] = $model->intervals[$i]['end'] ?? $defaultEnd;
        ?>
        <div class="schedule-interval <?= $model->intervals[$i]['is_enabled'] ? '' : 'disabled' ?>"
             data-day="<?= $i ?>">
            <div class="schedule-interval-title <?= $model->intervals[$i]['is_enabled'] ? 'selected' : '' ?>">
                <?= Html::activeCheckbox($model, "intervals[$i][is_enabled]",
                    [
                        'label'    => Html::tag('span', $intervalLabels[$i] ?? "",
                            ['class' => 'schedule-interval-label']),
                        'class'    => 'schedule-interval-is_enabled',
                        'disabled' => !key_exists($i, $intervalLabels)
                    ])
                ?>
            </div>
            <div class="control-group">
                <label class="control-label">
                    <?= Yii::t('app', 'Working Hours') ?>
                </label>
                <div class="controls">
                    <?= yii\widgets\MaskedInput::widget([
                        'model'     => $model,
                        'attribute' => "intervals[{$i}][start]",
                        'mask'      => '99:99',
                        'options'   => [
                            'placeholder' => "чч:мм",
                            'style'       => 'width: 50px',
                            'value'       => $model->intervals[$i]['start']
                        ]
                    ]) ?>

                    <span style="margin-left: 1px; margin-right: 1px;">:</span>

                    <?= \yii\widgets\MaskedInput::widget([
                        'model'     => $model,
                        'attribute' => "intervals[{$i}][end]",
                        'mask'      => '99:99',
                        'options'   => [
                            'placeholder' => "чч:мм",
                            'style'       => 'width: 50px',
                            'value'       => $model->intervals[$i]['end']
                        ]
                    ]) ?>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label">
                    <?= Yii::t('app', 'Break') ?>
                </label>
                <div class="controls">
                    <?= yii\widgets\MaskedInput::widget([
                        'model'     => $model,
                        'attribute' => "intervals[{$i}][break_start]",
                        'mask'      => '99:99',
                        'options'   => [
                            'placeholder' => "чч:мм",
                            'style'       => 'width: 50px',
                            'value'       => $model->intervals[$i]['break_start'] ?? null
                        ]
                    ]) ?>

                    <span style="margin-left: 1px; margin-right: 1px;">:</span>

                    <?= \yii\widgets\MaskedInput::widget([
                        'model'     => $model,
                        'attribute' => "intervals[{$i}][break_end]",
                        'mask'      => '99:99',
                        'options'   => [
                            'placeholder' => "чч:мм",
                            'style'       => 'width: 50px',
                            'value'       => $model->intervals[$i]['break_end'] ?? null
                        ]
                    ]) ?>
                </div>
            </div>
        </div>

        <?php if ($i !== 7) { ?>
            <div class="vertical-separator"></div>
        <?php } ?>
    <?php } ?>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Generate'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>

<?php
$form->end();

$allIntervals = json_encode(ScheduleTemplateHelper::allIntervals());
$js = <<<JS
var intervals = $allIntervals;
$('.schedule-interval-is_enabled').change(function(event) {
	if(this.checked) {
	    $(event.target).closest('.schedule-interval').removeClass('disabled');
	    $(event.target).closest('.schedule-interval-title').addClass('selected');
	} else {
	    $(event.target).closest('.schedule-interval').addClass('disabled');
	    $(event.target).closest('.schedule-interval-title').removeClass('selected');
	}
});

$('#type').change(function(e) {
    var type = this.value;
    if (intervals[type] !== undefined) {
        var selectedDays = Object.keys(intervals[type]);
        $.each($('.schedule-interval'), function(ind, el) {
            var day = $(el).data('day').toString();
            if (selectedDays.indexOf(day) !== -1) {
                $(el).removeClass('disabled');
                $(el).find('.schedule-interval-title :checkbox').prop('disabled', false);
                $(el).find('.schedule-interval-label').text(intervals[type][day]);
            } else {
                $(el).addClass('disabled');
                $(el).find('.schedule-interval-title').removeClass('selected');
                $(el).find('.schedule-interval-title :checkbox').prop('checked', false);
                $(el).find('.schedule-interval-title :checkbox').prop('disabled', true);
                $(el).find('.schedule-interval-label').text("");
            }
        });
    }
});
$('#division_id').change(function(e) {
  window.location.href = updateQueryStringParameter(window.location.href, "division_id", this.value);
});

function updateQueryStringParameter(uri, key, value) {
      var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
      var separator = uri.indexOf('?') !== -1 ? "&" : "?";
      if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
      }
      else {
        return uri + separator + key + "=" + value;
      }
    }
JS;
$this->registerJs($js);
