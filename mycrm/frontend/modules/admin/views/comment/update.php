<?php

use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\medCard\MedCardComment */
/* @var $diagnoses core\models\medCard\MedCardDiagnosis[] */
/* @var $categories core\models\medCard\MedCardCommentCategory[] */

$this->title                   = Yii::t('app', 'Comment Template');
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="comment-template-update">

    <div class="comment-template-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'category_id')
            ->dropDownList(
                ArrayHelper::map($categories, 'id', 'name'),
                ['class' => 'form-control', 'prompt' => Yii::t('app', 'Select')]
            ) ?>

        <?= $form->field($model, 'diagnosis_ids')
            ->widget(Select2::className(), [
                'options'       => ['multiple' => true],
                'pluginOptions' => [
                    'allowClear' => true,
                    'ajax'       => [
                        'url'            => \yii\helpers\Url::to(Yii::$app->params['api_host']
                            . '/v2/diagnosis'),
                        'dataType'       => 'json',
                        'data'           => new JsExpression('function(params) { return {q:params.term}; }'),
                        'processResults' => new JsExpression('function (data, params) {
                             var results = $.map(data, function(obj){
                                obj.text = obj.text || obj.name;
                                return obj;
                             });
                             return {results: results};
                         }'),
                    ],
                ],
            ]); ?>

        <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Update'),
                ['class' => 'btn btn-primary']) ?>
            <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id],
                [
                    'class' => 'btn btn-danger pull-right',
                    'data'  => [
                        'confirm' => Yii::t('app',
                            'Are you sure you want to delete this item?'),
                        'method'  => 'post',
                    ],
                ]) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
