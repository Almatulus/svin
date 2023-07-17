<?php

use core\helpers\OrderHelper;
use core\models\company\Company;
use core\models\Country;
use core\models\Payment;
use core\models\ServiceCategory;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model \core\forms\division\DivisionUpdateForm */
/* @var $form yii\widgets\ActiveForm */

$optionalOptions = [
    'class' => 'control-group optional hidden',
    'tag' => 'li'
];
?>

    <div class="division-form">

        <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'options' => ['tag' => 'li', 'class' => 'control-group'],
                'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
                'wrapperOptions' => ['class' => 'controls'],
            ],
            'options' => ['class' => 'simple_form']
        ]); ?>

        <?= $form->errorSummary($model); ?>

        <ol>
            <?php
            if (Yii::$app->user->can('divisionAdmin')):
                echo $form->field($model, 'company_id')
                    ->widget(Select2::className(), [
                        'data' => ArrayHelper::map(Company::find()->orderBy('name')->all(), 'id', 'name'),
                    ]);
            endif;

            echo $form->field($model, 'country_id')
                      ->dropDownList(ArrayHelper::map(Country::find()->where(['active' => true])->all(), 'id', 'name'),
                          ['prompt' => Yii::t('app', 'Select country')]
                      );
            echo $form->field($model, 'city_id')
                      ->widget(DepDrop::className(), [
                          'data' => isset($model->division) ? [$model->city_id => $model->division->city->name] : [],
                          'pluginOptions' => [
                              'depends'     => [Html::getInputId($model, 'country_id')],
                              'placeholder' => Yii::t('app', 'Select city'),
                              'url'         => Url::to(['/country/list']),
                              'initialize'  => true,
                              'loading'     => true
                          ]
                      ]);
            echo $form->field($model, 'address')->textarea(['maxlength' => true, 'rows' => 5]);
            echo $form->field($model, 'name')->textInput(['maxlength' => true]);
            echo $form->field($model, 'category_id')
                      ->dropDownList(ArrayHelper::map(ServiceCategory::getRootCategories(), 'id', 'name'),
                          ['prompt' => Yii::t('app', 'Select type')])
            ?>

            <?= $form->field($model, 'working_start')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '99:99',
            ]) ?>

            <?= $form->field($model, 'working_finish')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '99:99',
            ]) ?>

            <?= $form->field($model, 'payments')->widget(\kartik\select2\Select2::className(), [
                'data' => Payment::getPaymentsList(),
                'options' => ['multiple' => true],
                'size' => 'sm',
                'pluginOptions' => [
                    'width' => 'resolve',
                ]
            ]) ?>

            <?= $form->field($model, 'default_notification_time')->dropDownList(OrderHelper::getNotificationTimeList()); ?>

            <?= $form->field($model, 'notification_time_before_lunch')->widget(\yii\widgets\MaskedInput::className(), [
                'mask'    => '99:99',
                'options' => ['class' => 'form-control small-input']
            ]) ?>

            <?= $form->field($model, 'notification_time_after_lunch')->widget(\yii\widgets\MaskedInput::className(), [
                'mask'    => '99:99',
                'options' => ['class' => 'form-control small-input']
            ]) ?>

            <li class="control-group file optional">
                <label class="file optional control-label" for="staff_avatar">Логотип (макс. 1Мб)</label>
                <div class="controls">
                    <div class="btn fileinput-button js-image-field-wrapper">
                        <span class="icon sprite-add_photo_blue"></span>
                        <span><?= $model->logo_id === null ? Yii::t('app', 'Add') : Yii::t('app', 'Update') ;?></span>
                        <?= $form->field($model, 'image_file', [
                            'options' => [
                                'tag' => null,
                            ]
                        ])->fileInput([
                            'class'    => 'js-image-field',
                            'template' => '{input}'
                        ])->label(false) ?>
                    </div>
                    <span class="chosen_photo hidden">
                        Выбранное фото:<span class="photo_name"></span>&nbsp; &nbsp;
                        <a href="javascript:void(0)">Изменить</a>
                    </span>
                </div>
                <?php if ($model->logo_id !== null): ?>
                    <div class="avatar">
                        <?= Html::img(
                            $model->division->logo->getPath(),
                            ['height' => 150]
                        ) ?>
                    </div>
                <?php endif; ?>
            </li>

            <a href="javascript:;" type="button" id="btn-show-more" data-toggle="optional">
                Больше информации о заведении
            </a>

            <br>
            <br>

            <li class="control-group string optional hidden">
                <div class="controls">
                    <?php
                    foreach ($model->phones as $key => $phone) {
                        $index     = $key + 1;
                        $btnClass  = "btn-danger remove-item";
                        $iconClass = "fa-minus";
                        $label     = "";
                        if ($key == 0) {
                            $btnClass  = "btn-primary add-item";
                            $iconClass = "fa-plus";
                            $label     = $model->getAttributeLabel('phones');
                        }
                        echo $form->field($model, 'phones[]', [
                            'template' => "{label}\n
                                <div style='display:inline-block; width: 240px;'>
                                    <div class='input-group input-group-sm'>
                                        {input}
                                        <div class='input-group-btn'>
                                            <a role='button' class=\"btn {$btnClass}\" data-target=\"phones-{$index}\" style='height:28px'>
                                                <span class=\"fa {$iconClass}\" style='margin-right:0;pointer-events:none'></span>
                                            </a>
                                        </div>
                                    </div>\n
                                    {error}
                                </div>",
                            'options' => ['id' => "phones-{$index}", 'tag' => 'div', 'class' => ""]
                        ])->widget(\yii\widgets\MaskedInput::className(), [
                            'mask' => '+7 999 999 99 99',
                            'options' => ['style' => 'width:209px', 'value' => $phone],
                            'clientOptions' => [
                                'clearIncomplete' => true
                            ]
                        ])->label($label);
                    }
                    ?>
                </div>
            </li>

            <?php
            echo $form->field($model, 'url', [
                'options' => $optionalOptions
            ])->textInput(['maxlength' => true]);
            echo $form->field($model, 'description', [
                'options' => $optionalOptions
            ])->textarea(['maxlength' => true, 'rows' => '5']);
            ?>

            <li class="control-group string optional hidden">
                <div class="controls">
                    <label class="control-label" for="division-map">Карта</label>
                    <div id="divisionMap" style="width: 700px; height: 500px; display:inline-block"></div>
                </div>
            </li>
        </ol>

        <?php
        echo $form->field($model, 'latitude', [
            'options' => ['tag' => null]
        ])->hiddenInput()->label(false)->error(false);
        echo $form->field($model, 'longitude', [
            'options' => ['tag' => null]
        ])->hiddenInput()->label(false)->error(false);
        ?>

        <div class="form-actions">
            <div class="with-max-width">
                <div class="pull_right cancel-link">
                    <?= Html::a('Отмена', Yii::$app->request->referrer ? Yii::$app->request->referrer : ['index']) ?>
                </div>
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$coordinate = \yii\helpers\Json::encode([$model->latitude, $model->longitude]);
$js         = <<<JS
    $(function() {
        var myMap;
        ymaps.ready(function () {
            myMap = new ymaps.Map("divisionMap", {
                center: {$coordinate},
                zoom: 12,
                behaviors: ['scrollZoom', 'drag'],
            });
            var placemark = new  ymaps.Placemark({$coordinate});
            myMap.geoObjects.add(placemark);

            myMap.events.add('click', function (e) {
                var position = e.get('coords');
                placemark.geometry.setCoordinates(position);
                $("#division-latitude").val(position[0]);
                $("#division-longitude").val(position[1]);
            });
        });
    });
JS;

/** @var View $this */
$this->registerJsFile("https://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU", ['position' => View::POS_HEAD]);
$this->registerJs($js, View::POS_READY);
?>