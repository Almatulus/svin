<?php

use core\models\division\Division;
use core\models\ServiceCategory;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

$divisions  = Division::find()->select('category_id')->company()->enabled()->asArray()->all();
$categories = ArrayHelper::getColumn($divisions, 'category_id');

$items = ServiceCategory::find()->where([
    'OR',
    ['{{%service_categories}}.id' => $categories],
    ['{{%service_categories}}.parent_category_id' => $categories]
])
    ->staticType()
    ->select(['{{%service_categories}}.id', '{{%service_categories}}.name'])
    ->orderBy('{{%service_categories}}.name ASC')
    ->asArray()
    ->all();

if (sizeof($items) == 1) {
    $model->parent_category_id = $items[0]['id'];
}
?>

<div class='inner'>
    <?php $form = ActiveForm::begin(['id' => 'category-form', 'options' => ['class' => 'simple_form']]); ?>

    <ol>
        <li class="control-group string required service_category_name">
            <div class='controls'>
                <?= $form->field($model, 'name', [
                    'options' => ['class' => ''],
                    'template' => "{label}\n{input}\n{error}",
                    'inputOptions' => ['style' => 'width:240px'],
                    'errorOptions' => ['class' => 'help-block', 'style' => 'margin:0']
                ]); ?>
            </div>
        </li>
        <li class="control-group string required service_category_parent_id">
            <div class='controls'>
                <?= $form->field($model, 'parent_category_id', [
                    'options' => ['class' => ''],
                    'template' => "{label}\n{input}\n{error}",
                    'inputOptions' => ['style' => 'width:240px'],
                    'errorOptions' => ['class' => 'help-block', 'style' => 'margin:0']
                ])->dropDownList(ArrayHelper::map($items, 'id', 'name'),
                    ['prompt' => Yii::t('app', 'Select category')]); ?>
            </div>
        </li>
    </ol>
    <?php $form->end(); ?>
</div>