<?php
/**
 * CompanyCashflow model deletion view.
 *
 * @var $this yii\web\View
 * @var $model core\models\finance\CompanyCashflow
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title                   = $model->date;
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::t('app', 'Company Cashflows'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-cashflow-update">

<?php $form = ActiveForm::begin(
    [
    'fieldConfig' => [
        'options' => ['tag' => 'li', 'class' => 'control-group'],
        'template' => "{label}{beginWrapper}{input}\n{hint}\n{error}{endWrapper}",
        'wrapperOptions' => ['class' => 'controls'],
    ],
    'options' => ['class' => 'simple_form']
    ]
); ?>
<p>
    <?php echo Yii::t('app', 'Are sure want to delete?') ?>
</p>
<div class="box-buttons">
    <?php echo Html::submitButton(Yii::t('app', 'Delete'), ['class' => 'btn btn-primary']) ?>
    <div class="pull-right">
        <?php echo Html::a(Yii::t('app', 'Cancel'), Yii::$app->request->referrer) ?>
    </div>
<div>
<?php ActiveForm::end(); ?>

</div>
