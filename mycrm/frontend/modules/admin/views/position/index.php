<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Company positions');
$this->params['breadcrumbs'][] = "<h1>{$this->title}</h1>";
?>
<div class="company-position-index">

    <div class="actions">
        <?= Html::a(Yii::t('app', 'Create'), ['create'],
        ['class' => 'btn btn-primary']) ?>
    </div>

    <?php Pjax::begin(['id' => 'js-position-container',]) ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id'           => 'js-position-gridview',
        'columns'      => [
            'name',
            'description:ntext',
            [
                'class'    => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}'
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>

</div>

<?php
$js = <<<JS
    var deleteMultipleURL = 'delete-multiple';
	$('.link_box a').on('click', function(e) {
		var label = $( e.target ).closest('.sort_row').find('.lbl').html();
		var sort = $(this).html();
		$('.btn-sort').html(label + '<strong>' + sort + '</strong>' + '<b class="caret"></b>');
		$('.link_box .selected').removeClass('selected');
		$(this).addClass('selected');
	});

    $('.actions').on('click', '.js-button-delete:not(.disabled)', function() {
        if(confirm("Вы уверены что хотите удалить выбранные должности?") == true) {
            var ids = $('#js-position-gridview').yiiGridView('getSelectedRows');
            $.ajax({
                url: deleteMultipleURL,
                data: {ids: ids},
                type: 'post',
                success: function (response) {
                    $('#js-position-container').addClass('loading');
                    $.pjax.reload({
                        container:"#js-position-container",
                        timeout: 10000
                    });
                },
                error: function (response) {
                    $.jGrowl(response.message, { group: "flash_alert"});
                }
            });
        } else {
            //alert("You pressed Cancel!");
        }
    });
JS;
$this->registerJs($js);
?>
