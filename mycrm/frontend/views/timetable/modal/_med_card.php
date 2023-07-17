<?php
/**
 * @var Company $company
 */

use core\forms\order\OrderForm;
use core\models\company\Company;
use core\models\medCard\MedCardToothDiagnosis;
use core\models\ServiceCategory;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$company          = Yii::$app->user->identity->company;
$medCardDiagnoses = MedCardToothDiagnosis::find()
                                         ->where(['company_id' => $company->id])
                                         ->all();
$diagnoses        = ArrayHelper::map($medCardDiagnoses, 'id', 'name');
$abbreviations    = ArrayHelper::map($medCardDiagnoses, 'id', 'abbreviation');
$colors           = ArrayHelper::map($medCardDiagnoses, 'id', 'color');

Modal::begin([
    "id"            => "medcard-modal",
    'closeButton'   => [
        'label'        => Yii::t('app', 'Close'),
        'class'        => 'btn btn-default pull-right js-close-medcard',
        'data-dismiss' => null
    ],
    'header'        => '<h2 class="modal-title">Дневник</h2>',
    'options'       => [
        'class'    => 'medcard',
        'tabindex' => '',
        'style'    => 'overflow-y: auto;'
    ],
    "footer"        => Html::button(
        Yii::t('app', 'Print'),
        ['class' => 'btn btn-primary js-print-medcard']
    ),
    'footerOptions' => ['style' => 'background: #fff;']
]);
echo $this->render('_teeth_view', [
    'company'       => $company,
    'abbreviations' => $abbreviations,
    'colors'        => $colors,
]);
Modal::end();

Modal::begin([
    "id"            => "medcard-tab-modal",
    'closeButton'   => [
        'label'        => Yii::t('app', 'Close'),
        'class'        => 'btn btn-default pull-right js-close-medcard-tab',
        'data-dismiss' => null
    ],
    'header'        => '<h2 class="modal-title">Диагноз</h2>',
    "footer"        => Html::button(Yii::t('app', 'Save'),
        ['class' => 'btn btn-success pull-right js-save-medcard-tab']),
    'options'       => [
        'class'    => 'medcard',
        'tabindex' => '',
        'style'    => 'overflow-y: auto;'
    ],
    'footerOptions' => ['style' => 'background: #fff;']
]);
$form = ActiveForm::begin(['id' => 'medcard-tab-form']);
if ($company->category_id === ServiceCategory::ROOT_CLINIC) {
    echo $this->render('_teeth_form', [
        'diagnoses'     => $diagnoses,
        'abbreviations' => $abbreviations,
        'company'       => $company,
        'colors'        => $colors,
    ]);
}
echo $this->render('_services', ['form' => $form]);
echo $this->render('_comments', ['form' => $form, 'model' => new OrderForm()]);
$form->end();
Modal::end();

Modal::begin([
    "id"            => "medcard-teeth-history",
    'header'        => '<h2 class="modal-title">История зуба #<span id="js-teeth-number">?</span></h2>',
    'closeButton'   => [
        'label'        => Yii::t('app', 'Close'),
        'class'        => 'btn btn-default pull-right js-close-teeth-history',
        'data-dismiss' => null
    ],
    'options'       => [
        'class'    => 'medcard',
        'tabindex' => '',
        'style'    => 'overflow-y: auto;'
    ],
    'footerOptions' => ['style' => 'background: #fff;']
]);
?>
<?php
Modal::end();