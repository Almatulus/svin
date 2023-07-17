<?php
use core\helpers\color\ColorSelect2;
use core\models\customer\CustomerCategory;
use yii\web\JsExpression;

/* @var $this yii\web\View */
?>

<div id="js-modal-category" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <i class="fa fa-users fa-5x text-muted"></i>
                <h2 class="modal-title"><?=Yii::t('app','Add to categories')?></h2>
                <small>Будет добавлен <span class="js-modal-customer-size"></span> клиент</small>
            </div>
            <div class="modal-body">
                <?php
                    echo ColorSelect2::widget([
                        'name' => 'widget-categories',
                        'data' => CustomerCategory::getCategoryMapSelect2(),
                        'options' => ['multiple' => true, 'placeholder' => Yii::t('app','Select category'), 'id' => 'js-modal-category-items'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'templateSelection' => new JsExpression('formatRepoSelection'),
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        ],
                        'showToggleAll' => false,
                        'maintainOrder' => true,
                    ]);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=Yii::t('app','Cancel')?></button>
                <button id="js-modal-category-submit" type="button" class="btn btn-success"><?=Yii::t('app','Add')?></button>
            </div>
        </div>
    </div>
</div>