<?php

use core\models\user\User;
use core\models\division\Division;
use core\models\warehouse\Category;
use core\models\warehouse\ProductType;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\models\warehouse\Stocktake */
/* @var $form yii\widgets\ActiveForm */

$backUrl = Yii::$app->request->referrer;
if (!$backUrl) {
    $backUrl = ['index'];
}
?>
<style>
    .simple_form .help-block {
        margin: 0;
    }
</style>

<?php $form = ActiveForm::begin([
    'fieldConfig' => [
        'options' => ['tag' => 'li', 'class' => 'control-group'],
        'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
        'wrapperOptions' => ['class' => 'controls'],
    ],
    'options' => ['class' => 'simple_form']
]); ?>

<div class="column_row">
    <fieldset>
        <ol>
            <?= $form->field($model, 'type_of_products')->dropDownList(ProductType::map(), [
                'prompt' => Yii::t('app', 'All')
            ]) ?>
            <?= $form->field($model, 'category_id')->dropDownList(Category::map(), [
                'prompt' => Yii::t('app', 'All Categories')
            ]) ?>
            <?= $form->field($model, 'name')->textInput() ?>
            <?= $form->field($model, 'division_id')->dropDownList(Division::getOwnDivisionsNameList()) ?>
            <?= $form->field($model, 'creator_id')->dropDownList(
                ArrayHelper::map(User::find()->company()->enabled()->all(), 'id', 'name')
            ) ?>
            <?= $form->field($model, 'description')->textarea() ?>
        </ol>
    </fieldset>
</div>

<div class="form-actions">
    <div class="with-max-width">
        <button class="btn btn-primary" type="submit">
            <?= Yii::t('app', 'Begin stocktake') ?>
        </button>
        <?= Html::a(Yii::t('app', 'Cancel'), $backUrl, ['class' => 'btn']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
