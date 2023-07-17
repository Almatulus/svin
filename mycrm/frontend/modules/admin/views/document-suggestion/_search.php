<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\admin\search\DocumentSuggestionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="document-suggestion-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
    <div class="col-sm-6">
            <div class="form-group">
                <?= Html::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
            </div>
        </div>
        <div class="col-sm-6 pull-left">
            <?= $form->field($model, 'text')->textInput(['placeholder' => Yii::t('app', 'Search')])->label(false) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
