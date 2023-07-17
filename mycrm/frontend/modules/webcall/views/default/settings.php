<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model core\forms\webcall\WebCallUpdateForm */

$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Web Calls'), 'url' => ['calls']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-create">

    <div class="company-form">
        <?php
        $form = ActiveForm::begin([
            'id' => 'dynamic-form',
            'fieldConfig' => [
                'inlineRadioListTemplate' => "{label}{beginWrapper}{input}\n{error}\n{hint}{endWrapper}",
                'options' => ['tag' => 'li', 'class' => 'control-group'],
                'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
                'wrapperOptions' => ['class' => 'controls'],
            ],
            'options' => ['class' => 'simple_form']
        ]); ?>
        <?= $form->errorSummary($model); ?>
        <div class="row">
            <div class="col-sm-8 simple_form">
                <ol>
                    <?php
                    echo $form->field($model, 'username')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'api_key')->textInput(['class' => 'string options', 'maxlength' => true]);
                    echo $form->field($model, 'domain')->textInput(['class' => 'string options', 'maxlength' => true]);
                    ?>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <?= \yii\grid\GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns'      => [
                        'id',
                        'name',
                        'email',
                        [
                            'attribute' => 'division_id',
                            'value'     => 'division.name',
                        ],
                        [
                            'class'      => 'yii\grid\ActionColumn',
                            'controller' => 'account',
                            'template'   => '{delete}'
                        ]
                    ]
                ]) ?>
            </div>
        </div>

        <div class="form-actions">
            <div class="with-max-width">
                <?= Html::submitButton(Yii::t('app', 'Save'), [
                    'class' => 'btn btn-primary',
                    'name' => 'submit-button'
                ]) ?>
                <?= Html::a(Yii::t('app', 'Create account'),
                    ['/webcall/account/create'], [
                        'class' => 'btn btn-default'
                    ])
                ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

</div>
