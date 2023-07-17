<?php

use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\medCard\MedCardComment */
/* @var $diagnoses core\models\medCard\MedCardDiagnosis[] */
/* @var $categories core\models\medCard\MedCardCommentCategory[] */

$this->title = Yii::t('app', 'Comment Template');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Comment Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comment-template-create">

    <div class="comment-template-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'category_id')
            ->dropDownList(
                ArrayHelper::map($categories, 'id', 'name'),
                ['class' => 'form-control', 'prompt' => Yii::t('app', 'Select')]
            ) ?>

        <?= $form->field($model, 'diagnosis_ids')
            ->widget(Select2::className(), [
                'data' => ArrayHelper::map($diagnoses, 'id', 'name'),
                'options' => ['multiple' => true],
                'pluginOptions' => ['allowClear' => true],
            ]); ?>

        <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Create'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
