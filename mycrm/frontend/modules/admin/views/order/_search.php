<?php


use core\models\company\Company;
use core\helpers\order\OrderConstants;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\order\search\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-4">
            <?php echo $form->field($model, 'start')->widget(DatePicker::className(), [
                'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-4">
            <?php echo $form->field($model, 'end')->widget(DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
        <div class="col-sm-4">
            <?php echo $form->field($model, 'number') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <?php echo $form->field($model, 'company_id')->widget(Select2::className(), [
                'data' => ArrayHelper::map(Company::find()->all(), 'id', 'name'),
                'pluginOptions' => ['allowClear' => true],
                'options' => ['prompt' => ''],
            ]); ?>
        </div>
        <div class="col-sm-4">
            <?php echo $form->field($model, 'type')
                ->dropDownList(OrderConstants::getTypes(), ['prompt' => Yii::t('app', 'Select types')]) ?>
        </div>
        <div class="col-sm-4">
            <?php echo $form->field($model, 'status')
                ->dropDownList(OrderConstants::getStatuses(), ['prompt' => Yii::t('app', 'Select status')]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="form-group pull-right">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
            <div class="inline_block">
                <div class="dropdown">
                    <button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">Действия <b class="caret"></b></button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= Html::a('<i class="fa fa-cloud-download-alt"></i> ' . Yii::t('app', 'Import from Excel'), '#', ['id' => 'js-import',]) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
