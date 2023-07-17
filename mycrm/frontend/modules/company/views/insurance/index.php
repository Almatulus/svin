<?php

use core\models\company\Insurance;
use core\models\InsuranceCompany;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \core\forms\insurance\InsuranceForm */

$this->title = Yii::t('app', 'Insurance Companies');
$this->params['breadcrumbs'][] = "<i class='fa fa-medkit'></i>&nbsp;<h1>{$this->title}</h1>";
?>
<div class="insurance-index">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'simple_form']
    ]); ?>

    <div id="insurance_companies_tree"></div>

    <div class="form-actions">
        <div class="with-max-width">
            <button class="btn btn-primary" type="submit">
                <span class="icon sprite-add_customer_save"></span>Сохранить
            </button>
        </div>
    </div>

    <?php ActiveForm::end() ?>

</div>

<?php

$source = InsuranceCompany::find()->select(["name AS title", "id AS key"])->asArray()->all();
$selectedCompanyIds = Insurance::find()->where([
    'company_id'   => Yii::$app->user->identity->company_id,
    'deleted_time' => null
])->select('insurance_company_id')->column();

foreach ($source as $index => $item) {
    if (in_array($item['key'], $selectedCompanyIds)) {
        $source[$index]['selected'] = true;
    }
}

$source = json_encode($source);

$js = <<<JS
initializeTree("#insurance_companies_tree", {$source}, loadError);

$("form").submit(function() {
    var selection = jQuery.each(
        jQuery('#insurance_companies_tree').fancytree('getRootNode').tree.getSelectedNodes(),
        function( key, node ) {
            $('.simple_form').append("<input type='hidden' name='InsuranceForm[companies][" + key + "]' value='" + node.key + "'>");
        }
    );  
    return true;
});
JS;

$this->registerJs($js);
?>
