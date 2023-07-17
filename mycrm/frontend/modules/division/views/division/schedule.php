<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title                   = Yii::t('app', 'Schedule');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 
    'label' => Yii::$app->user->identity->company->name,
    'url' => ['/company/default/update', 'id' => Yii::$app->user->identity->company_id]
];
$this->params['breadcrumbs'][] = [
    'label' => $division->name, 
    'url' => ['/division/division/update', 'id' => $division->key]
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="column-row calendar-body">
    <table class="week-table">
        <tbody>
            <tr class="schedule-form-row" style="display: table">
                <td>
                    <?php $form = \yii\widgets\ActiveForm::begin([
                        'fieldConfig' => [
                            'options' => ['tag' => null],
                            'template' => '{input}'
                        ],
                        'options' => ['class' => 'new_default_schedule_form']
                    ]); ?>
                        <div class="column_row schedule-errors"><?= $form->errorSummary($models); ?></div>
                        <div class="column_row schedule-periods">
                            <?php 
                            $counter = 0;
                            $models = ArrayHelper::index($models, null, 'day_num');
                            foreach($weekdays as $index => $day) { 
                                $periods = $models[$index];
                            ?>
                                <div class="schedule-period">
                                    <div class="calendar-weekday-col"><?= $day ?></div>
                                    <div class="calendar-form-col">
                                        <?php foreach ($periods as $key => $model) { ?>
                                            <?php 
                                            $style = "display: block";
                                            if (!$model->is_open) {
                                                $style = "display: none";
                                            }
                                            ?>
                                            <div class="column_row">
                                                <?= Html::activeHiddenInput($model, "[{$counter}]day_num", [
                                                    'value' => $index
                                                ]) ?>
                                                <div class="column column-working">
                                                    <?php 
                                                    if ($key == 0) { 
                                                        echo $form->field($model, "[{$counter}]is_open")->checkbox([
                                                            'class' => 'schedule-is_open'
                                                        ]);
                                                    } else { 
                                                        echo Html::activeHiddenInput($model, "[{$counter}]is_open");
                                                    } 
                                                    ?>
                                                </div>
                                                <div class="column column-valid-times" style="<?= $style ?>"">
                                                    <?= "&nbsp; с&nbsp;" . $form->field($model, "[{$counter}]from")->dropDownList($hours, [
                                                        'class' => 'schedule-time'
                                                    ])->label(false); ?>
                                                    <?= "&nbsp; до&nbsp;" . $form->field($model, "[{$counter}]to")->dropDownList($hours, [
                                                        'class' => 'schedule-time'
                                                    ])->label(false); ?>
                                                </div>
                                                <div class="column column-period-actions" style="<?= $style ?>"">
                                                    <?php if ($key == 0) { ?>
                                                        <div class="column-add-actions">
                                                            <?= Html::a(Yii::t('app', 'Add range'), '#', [
                                                                'class' => 'btn_downcase',
                                                                'data-row' => "<div class='column_row'> <input type='hidden' id='divisionschedule-NEW_ITEM-day_num' name='DivisionSchedule[NEW_ITEM][day_num]' value='{$index}'> <div class='column column-working'> <input type='hidden' name='DivisionSchedule[NEW_ITEM][is_open]' value='1'></div> <div class='column column-valid-times' style='display: block;'> &nbsp; с&nbsp; <select id='divisionschedule-NEW_ITEM-from' class='schedule-time' name='DivisionSchedule[NEW_ITEM][from]'> <option value='07:00'>07:00</option> <option value='08:00'>08:00</option> <option value='09:00'>09:00</option> <option value='10:00'>10:00</option> <option value='11:00'>11:00</option> <option value='12:00'>12:00</option> <option value='13:00'>13:00</option> <option value='14:00'>14:00</option> <option value='15:00'>15:00</option> <option value='16:00'>16:00</option> <option value='17:00'>17:00</option> <option value='18:00'>18:00</option> <option value='19:00'>19:00</option> <option value='20:00'>20:00</option> <option value='21:00'>21:00</option> <option value='22:00'>22:00</option> <option value='23:00'>23:00</option> </select> &nbsp; до&nbsp; <select id='divisionschedule-NEW_ITEM-to' class='schedule-time' name='DivisionSchedule[NEW_ITEM][to]'> <option value='07:00'>07:00</option> <option value='08:00'>08:00</option> <option value='09:00'>09:00</option> <option value='10:00'>10:00</option> <option value='11:00'>11:00</option> <option value='12:00'>12:00</option> <option value='13:00'>13:00</option> <option value='14:00'>14:00</option> <option value='15:00'>15:00</option> <option value='16:00'>16:00</option> <option value='17:00'>17:00</option> <option value='18:00'>18:00</option> <option value='19:00'>19:00</option> <option value='20:00'>20:00</option> <option value='21:00'>21:00</option> <option value='22:00'>22:00</option> <option value='23:00' selected=''>23:00</option> </select> </div> <div class='column column-period-actions' style='display: block;'> <a href='#' class='btn_downcase' data-destroy-item='true' title='удалить'><i class='icon sprite-delete'></i></a></div><div class='c'></div></div>"
                                                            ]);
                                                            ?>
                                                        </div>
                                                    <?php } else { ?>
                                                        <a href='#' class='btn_downcase' data-destroy-item='true' title='удалить'>
                                                            <i class='icon sprite-delete'></i>
                                                        </a>
                                                    <?php } ?>                                                    
                                                </div>
                                                <div class="c"></div>
                                            </div>
                                            <?php $counter++; ?>
                                        <?php } ?>
                                    </div>
                                    <div class="c"></div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="column_row footer_buttons">
                            <button class="btn btn_downcase btn_blue" type="submit">
                                <i class="icon sprite-add_customer_save"></i>Сохранить
                            </button>
                        </div>
                    <?php $form->end(); ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php

$js = <<<JS
    $(function() {
        var rangesCount = $('.calendar-form-col .column_row').length;

        $('.column-add-actions a').click(function(e) {
            e.preventDefault();

            var element = $(e.target);
            var row = element.data('row');

            row = $(row.replace(/NEW_ITEM/gi, rangesCount));
            row.find('.column-period-actions a').click(deleteRow);
            element.closest('.calendar-form-col').append(row);

            rangesCount++;
        });

        $('.schedule-is_open').on('change', function() {
            var element = $(this);
            var formColumn = element.closest(".calendar-form-col");

            if(this.checked) {
                formColumn.find('.column-period-actions').show();
                formColumn.find('.column-valid-times').show();
            } else {
                formColumn.find('.column_row').not(':first').remove();
                formColumn.find('.column-period-actions').hide();
                formColumn.find('.column-valid-times').hide();
            }
        });

        var deleteRow = function(e) {
            var element = $(e.target);
            var row = element.closest('.column_row');
            row.remove();
        };

        $('[data-destroy-item="true"]').click(deleteRow);
    });
JS;

$this->registerJs($js);