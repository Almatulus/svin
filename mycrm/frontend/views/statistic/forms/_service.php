<?php

use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\ServiceCategory;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model core\forms\customer\statistic\StatisticService */
?>

<div class="col-md-4">
    <?= $form->field($model, 'from', [
        'template' => '<div class="input-group"><span class="input-group-addon">'
            . Yii::t('app', 'From date') . '</span>{input}</div>',
    ])->widget(DatePicker::className(), [
        'type'          => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'autoclose' => true,
            'format'    => 'yyyy-mm-dd'
        ]
    ]) ?>
</div>
<div class="col-md-4">
    <?= $form->field($model, 'to', [
        'template' => '<div class="input-group"><span class="input-group-addon">' .
            Yii::t('app', 'To date') . '</span>{input}</div>',
    ])->widget(DatePicker::className(), [
        'type'          => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'autoclose' => true,
            'format'    => 'yyyy-mm-dd'
        ]
    ]) ?>
</div>
<div class="col-sm-4">
    <?php
    $divisions = Division::getOwnDivisionsNameList();
    if (sizeof($divisions) == 1) {
        $model->division = key($divisions);
    }
    ?>
    <?= $form->field($model, 'division')->widget(Select2::class, [
        'data'          => Division::getOwnDivisionsNameList(),
        'pluginOptions' => ['allowClear' => true],
        'options'       => ['placeholder' => Yii::t('app', 'All Divisions')],
        'size'          => Select2::SMALL
    ]) ?>
</div>
<div class="col-sm-4">
    <?php
    $data = $model->category ? ServiceCategory::find()->select([
        ServiceCategory::tableName() . '.id',
        ServiceCategory::tableName() . '.name'
    ])
        ->filterByDivision($model->division)
        ->byId($model->category)
        ->asArray()
        ->all() : [];
    $data = ArrayHelper::map($data, "id", "name");
    ?>

    <?= $form->field($model, 'category')->widget(DepDrop::className(), [
        'type' => DepDrop::TYPE_SELECT2,
        'data'          => $data,
        'pluginOptions' => [
            'depends'     => [Html::getInputId($model, 'division')],
            'url'         => Url::to(['/service-category/list']),
            'placeholder' => Yii::t('app', '--- Select categories ---'),
            'loadingText' => Yii::t('app', '--- Select categories ---'),
            'initialize'  => true,
        ],
        'options'       => [
            'placeholder' => Yii::t('app', '--- Select categories ---'),
            'multiple' => 'multiple'
        ],
    ]) ?>
</div>

<div class="col-sm-4">
    <?php
    $data = $model->division_service ? DivisionService::find()->select([
        DivisionService::tableName() . '.id',
        DivisionService::tableName() . '.service_name'
    ])
        ->division($model->division)
        ->byId($model->division_service)
        ->asArray()
        ->all() : [];
    $data = ArrayHelper::map($data, "id", "name");
    ?>
    <?= $form->field($model, 'division_service')->widget(DepDrop::className(), [
        'type' => DepDrop::TYPE_SELECT2,
        'data'          => $data,
        'pluginOptions' => [
            'depends'     => [Html::getInputId($model, 'division')],
            'url'         => Url::to(['/division/service/list']),
            'placeholder' => Yii::t('app', '--- Select services ---'),
            'loadingText' => Yii::t('app', '--- Select services ---'),
            'initialize'  => true,
        ],
        'options'       => [
            'placeholder' => Yii::t('app', '--- Select services ---'),
            'multiple' => 'multiple'
        ],
    ]) ?>
</div>

<div class="col-sm-2">
    <button type="submit" class="btn btn-primary" name="action" value="search"><?= Yii::t('app', 'Show statistics') ?></button>
</div>
<div class="col-sm-2">
    <button type="submit" class="btn" name="action" value="download"><?= Yii::t('app', 'Export') ?></button>
</div>