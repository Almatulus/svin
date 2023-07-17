<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel frontend\search\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $form yii\widgets\ActiveForm */
/* @var $fetchedCustomers integer */


$this->title                   = Yii::t('app', 'Customers');
$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => $this->title];
$this->params['bodyID']        = 'customers';

?>

<div class="customer-index">

<?php \yii\widgets\Pjax::begin([
    'id' => 'pjax-container',
    'timeout' => 10000,
    'clientOptions' => ['container' => 'pjax-container']]); ?>

    <?php
        echo Html::hiddenInput('js-fetched-customers',json_encode($fetchedCustomers));
        echo Html::hiddenInput('js-customers-count', $dataProvider->getTotalCount());
    ?>
    <div class="column_row row buttons-row">
        <div class="col-sm-7 col-xs-12 input-with-select-sm">
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
                'id' => 'js-activeform',
                'options' => ['data-pjax' => true ],
            ]); ?>
                <?= $this->render('_search_contact', ['model' => $searchModel, 'form' => $form]) ?>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col-sm-5 col-xs-12 right-buttons">
            <?= $this->render('_actions', []) ?>
        </div>
    </div>
    <div id="js-customers-gridview">
        <?= $this->render('_grid', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]) ?>
    </div>

<?php \yii\widgets\Pjax::end(); ?>

</div>

<iframe id="my_iframe" style="display:none;"></iframe>
<script>
    function Download(url) {
        document.getElementById('my_iframe').src = url;
    }
</script>

<?= $this->render('modals/_category', ['model' => $searchModel, 'form' => $form]) ?>
<?= $this->render('modals/_request', ['model' => $searchModel, 'form' => $form]) ?>

<?php
$js = <<<JS
	$('.link_box a').on('click', function(e) {
		var label = $( e.target ).closest('.sort_row').find('.lbl').html();
		var sort = $(this).html();
		$('.btn-sort').html(label + '<strong>' + sort + '</strong>' + '<b class="caret"></b>');
		
		$('.link_box .selected').removeClass('selected');
		$(this).addClass('selected');
	});
JS;
$this->registerJs($js);
?>