<?php

use core\helpers\GenderHelper;
use core\helpers\StaffHelper;
use core\models\company\CompanyPosition;
use core\models\division\Division;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \core\forms\staff\StaffUpdateForm */
/* @var $staff \core\models\Staff */
/* @var $form yii\widgets\ActiveForm */

$this->title                   = Yii::t('app', 'Staff');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $this->title,
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = [
    'label' => $model->name,
    'url'   => ['view', 'id' => $staff->id]
];
$this->params['breadcrumbs'][] = 'Редактировать';
$company_positions_list = ArrayHelper::map(CompanyPosition::getOwnCompanyPositions(), 'id', 'name');

$dateOptions = \core\helpers\DateHelper::dateOptions();
?>
<div class="staff-update">

    <div class="staff-form">

        <?php $form = ActiveForm::begin([
            'options' => [
                'class'   => 'simple_form',
                'enctype' => 'multipart/form-data'
            ]
        ]); ?>

        <?= $form->errorSummary($model); ?>
        <ol>
            <li class="control-group string staff_name">
                <div class="controls">
                    <?= $form->field($model, 'name')
                             ->textInput(['class' => 'string options']) ?>
                </div>
            </li>
            <li class="control-group string staff_surname">
                <div class="controls">
                    <?= $form->field($model, 'surname')
                             ->textInput(['class' => 'string options']) ?>
                </div>
            </li>

            <li class="control-group string staff_phone">
                <div class="controls">
                    <?= $form->field($model, 'phone')
                             ->widget(\yii\widgets\MaskedInput::className(), [
                                 'mask' => '+7 999 999 99 99',
                             ]) ?>
                </div>
            </li>

            <li class="control-group boolean staff_has_calendar">
                <?= $form->field($model, 'has_calendar', [
                    'options' => ['style' => 'display: inline-block;'],
                ])->checkbox(['class' => 'boolean control-label']) ?>
            </li>

            <li class="control-group color staff_color">
                <div class="controls">
                    <?= $form->field($model, 'color')
                             ->dropDownList(StaffHelper::getCssClasses(), [
                                 'class' => "simplecolorpicker picker color",
                                 'id'    => 'staff_color'
                             ]) ?>
                </div>
            </li>

            <li class="control-group select staff_division">
                <div class="controls">
                    <?= $form->field($model, 'division_ids')
                             ->widget(Select2::className(), [
                                 'data'    => Division::getCompanyDivisionsList(\Yii::$app->user->identity->company),
                                 'options' => [
                                     'multiple' => true
                                 ],
                             ]) ?>
                </div>
            </li>

            <li class="control-group string staff_services"
                data-name="staff_service_ids">
                <label class="string control-label" for="staff_service_ids">Услуги</label>
                <div class="controls">
                    <div id="services_tree"></div>
                    <input id="staff_service_ids"
                           name="Staff[division_service_ids]"
                           type="hidden">
                </div>
            </li>

            <li class="skip_count">
                <ol class="inner-inputs-list">
                    <h5>
                        <?php
                            $hidden = "none";
                            echo $form->field($model, 'create_user', [
                                'options' => ['style' => 'display: inline-block;'],
                            ])->checkbox(['class' => 'boolean control-label'],
                                false)->label(false);
                            echo " <b>" . Yii::t('app', 'Give access to system')
                                 . "</b>";
                        ?>
                    </h5>
                    <li class="control-group string staff_user_permissions"
                        style="display:<?= $hidden ?>"
                        data-name="staff_user_permissions">
                        <div class="controls">
                            <label class="string control-label"
                                   for="staff_user_permissions">Доступы</label>
                            <div class="controls">
                                <div id="permissions_tree"></div>
                                <input id="staff_user_permissions"
                                       name="Staff[user_permissions]"
                                       type="hidden">
                            </div>

                        </div>
                    </li>
                    <li class="control-group string staff_user_divisions"
                        style="display:<?= $hidden ?>">
                        <div class="controls">
                            <?= $form->field($model, 'user_divisions')
                                     ->widget(Select2::className(), [
                                         'data'    => Division::getCompanyDivisionsList(\Yii::$app->user->identity->company),
                                         'options' => ['multiple' => true],
                                         'size'    => 'sm'
                                     ]) ?>
                        </div>
                    </li>
                    <li class="control-group boolean staff_see_own_orders"
                        style="display:<?= $hidden ?>">
                        <?= $form->field($model, 'see_own_orders', [
                            'options' => ['style' => 'display: inline-block;'],
                        ])->checkbox(['class' => 'boolean optional control-label']) ?>
                    </li>
                    <li class="control-group boolean staff_create_order"
                        style="display:<?= $hidden ?>">
                        <?= $form->field($model, 'can_create_order', [
                            'options' => ['style' => 'display: inline-block;'],
                        ])->checkbox(['class' => 'boolean control-label']) ?>
                    </li>
                    <li class="control-group boolean staff_create_order"
                        style="display:<?= $hidden ?>">
                        <?= $form->field($model, 'can_update_order', [
                            'options' => ['style' => 'display: inline-block;'],
                        ])->checkbox(['class' => 'boolean control-label']) ?>
                    </li>
                    <li class="control-group boolean staff_see_customer_phones">
                        <?= $form->field($model, 'see_customer_phones', [
                            'options' => ['style' => 'display: inline-block;'],
                        ])->checkbox(['class' => 'boolean control-label']) ?>
                    </li>
                    <li class="control-group string staff_username">
                        <div class="controls">
                            <?= $form->field($model, 'username')
                                ->widget(\yii\widgets\MaskedInput::className(), [
                                    'mask' => '+7 999 999 99 99',
                                ]) ?>
                        </div>
                    </li>
                </ol>
            </li>

            <li class="control-group select optional">
                <div class="controls">
                    <?= $form->field($model, 'gender')
                             ->dropDownList(GenderHelper::getGenders()) ?>
                </div>
            </li>
            <li class="control-group select optional">
                <div class="controls">
                    <?= $form->field($model, 'birth_date')->widget(DateControl::classname(), $dateOptions); ?>
                </div>
            </li>
            <li class="control-group select optional">
                <div class="controls">
                    <?= $form->field($model, 'company_position_ids')
                             ->widget(Select2::classname(), [
                                 'data'          => $company_positions_list,
                                 'options'       => [
                                     'multiple' => true,
                                     'prompt' => Yii::t('app', 'Select company positions')
                                 ],
                                 'addon'         => [
                                     'append'       => [
                                         'content'  => Html::button('<span class="fa fa-plus"></span>',
                                             [
                                                 'class'   => 'btn btn-primary',
                                                 'title'   => 'Добавить новую должность',
                                                 'onclick' => 'addNewPosition()',
                                                 'style'   => 'display: inline-block; height: 28px'
                                             ]),
                                         'asButton' => true,
                                     ],
                                     'groupOptions' => [
                                         'style' => 'display: inline-block; max-width: 500px;'
                                     ]
                                 ],
                                 'size'          => 'sm',
                                 'pluginOptions' => [
                                     'allowClear' => false,
                                 ]
                             ]) ?>
                </div>
            </li>
            <li class="control-group string optional">
                <div class="controls">
                    <?= $form->field($model, 'code_1c')
                        ->textInput(['class' => 'string options']) ?>
                </div>
            </li>
            <li class="control-group string optional">
                <div class="controls">
                    <?= $form->field($model, 'description')->textArea([
                        'size'      => '50',
                        'class'     => 'string options',
                        'maxlength' => true
                    ]) ?>
                </div>
            </li>
            <li class="control-group string optional">
                <div class="controls">
                    <?= $form->field($model, 'description_private')->textArea([
                        'size'      => '50',
                        'class'     => 'string options',
                        'maxlength' => true
                    ]) ?>
                </div>
            </li>
            <li class="control-group file optional">
                <label class="file optional control-label" for="staff_avatar">Фотография</label>
                <div class="controls">
                    <div class="btn fileinput-button js-image-field-wrapper">
                        <span class="icon sprite-add_photo_blue"></span>
                        <span>Добавить фото</span>
                        <?= $form->field($model, 'image_file')
                                 ->fileInput(['class' => 'js-image-field']) ?>
                    </div>
                    <span class="chosen_photo hidden">
                        Выбранное фото:<span class="photo_name"></span>&nbsp; &nbsp;
                        <a href="javascript:void(0)">Изменить</a>
                    </span>
                </div>
            </li>
        </ol>
        <?php if (Yii::$app->request->referrer == null) {
            $url = ['index'];
        } else {
            $url = Yii::$app->request->referrer;
        }
        ?>
        <div class="form-actions fixed">
            <div class="with-max-width">
                <div class="pull_right cancel-link">
                    <?= Html::a('Отмена', $url) ?>
                </div>
                <button class="btn btn-primary" type="submit">
                    <span class="icon sprite-add_customer_save"></span>Сохранить
                </button>
                <button class="btn btn-default" type="submit" name="action"
                        value="add-another">
                    <?= Yii::t('app', 'Save and add new staff') ?>
                </button>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <?php
        $source_url     = Url::to(['/division/division/service']);
        $timetableTitle = Yii::t('app', 'Timetable');

        $source = Json::encode([
            "url"      => $source_url,
            "type"     => 'POST',
            "expanded" => true,
            "data"     => [
                'division_id[]' => ArrayHelper::getColumn(
                    $staff->divisions,
                    'id'
                ),
                'staff_id'      => $staff->id,
            ],
            "dataType" => 'json'
        ]);

        $permissions = StaffHelper::getPermissionsTree($model->user_permissions);
        $permissions = Json::encode($permissions);

        $js
            = <<<JS
        hideTimetableRemoveBtn();

        function hideTimetableRemoveBtn() {
            $("li[title=$timetableTitle]").find('.select2-selection__choice__remove').hide();
        }

        initializeTree("#services_tree", {$source}, loadError);
        initializeTree("#permissions_tree", {$permissions}, loadError);

		$("#staff-division_ids").on('change', function() {
			var division_id = $(this).val();

			var newSourceOption = {
				url: '{$source_url}', // Here is link for loading services from division
				type: 'POST',
				data: {
				    division_id: division_id,
				    staff_id: {$staff->id}
				},
				dataType: 'json'
			};

			var tree = $('#services_tree').fancytree('getTree');
			tree.reload(newSourceOption);
		});
JS;
        $this->registerJs($js);
        ?>
    </div>


</div>
