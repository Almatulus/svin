<?php

use core\models\division\Division;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model core\forms\customer\statistic\StatisticService | core\forms\customer\statistic\StatisticCustomer */
/* @var $categories array */
?>

<div class="col-md-3">
    <?= $form->field($model, 'from', [
        'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                'From date') . '</span>{input}</div>',
    ])->widget(DatePicker::className(), [
        'type'          => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'autoclose' => true,
            'format'    => 'yyyy-mm-dd'
        ]
    ]) ?>
</div>
<div class="col-md-3">
    <?= $form->field($model, 'to', [
        'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                'To date') . '</span>{input}</div>',
    ])->widget(DatePicker::className(), [
        'type'          => DatePicker::TYPE_INPUT,
        'pluginOptions' => [
            'autoclose' => true,
            'format'    => 'yyyy-mm-dd'
        ]
    ]) ?>
</div>
<div class="col-sm-2">
    <?= $form->field($model, 'category')->dropDownList($categories, [
        'prompt' => Yii::t('app', 'All Categories')
    ]) ?>
</div>
<div class="col-sm-2">
    <?= $form->field($model, 'division')->dropDownList(Division::getOwnDivisionsNameList(), [
        'prompt' => Yii::t('app', 'All Divisions')
    ]) ?>
</div>
<div class="col-sm-2">
    <button type="submit" class="btn btn-primary"><?= Yii::t('app', 'Search') ?></button>
</div>