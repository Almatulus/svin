<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
?>

<div id="js-modal-request" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <div class="modal-content">
            <div class="modal-header text-center">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <i class="fa fa-envelope fa-5x text-muted"></i>
                <h2 class="modal-title"><?=Yii::t('app','Send SMS')?></h2>
                <small>Будет отправлен клиентам: <span class="js-modal-customer-size"></span></small>
            </div>
            <div class="modal-body">
                <p><b>Введите текст SMS</b></p>
                <?= Html::textarea('widget-request','',[
                    'placeholder' => Yii::t('app','Send SMS'),
                    'id' => 'js-modal-request-text',
                    'class' => 'form-control'
                ]);?>
                <br>
                <small><span class="js-modal-request-symbols"></span> символ(ов) -
                    <span class="js-modal-request-count"></span> SMS</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=Yii::t('app','Cancel')?></button>
                <button id="js-modal-request-submit" type="button" class="btn btn-primary"><?=Yii::t('app','Send')?></button>
            </div>
        </div>

    </div>
</div>