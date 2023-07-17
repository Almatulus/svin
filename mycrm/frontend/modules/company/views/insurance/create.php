<?php

use core\models\InsuranceCompany;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model core\models\company\Insurance */

$this->title                   = Yii::t('app', 'Create Insurance');
$this->params['breadcrumbs'][] = [
    'template' => '<li><i class="fa fa-medkit"></i>&nbsp;{link}</li>',
    'label'    => Yii::t('app', 'Insurances'),
    'url'      => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="insurance-create">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'insurance_company_id')
        ->widget(Select2::className(), [
            'data'          => InsuranceCompany::map(false),
            'pluginOptions' => [
                'allowClear' => false,
                'width'      => '240px',
            ],
            'showToggleAll' => false,
            'theme'         => Select2::THEME_DEFAULT
        ])
    ?>

    <div class="form-group">
        <?= Html::submitButton(
            Yii::t('app', 'Create'),
            ['class' => 'btn btn-success']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
