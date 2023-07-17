<?php
use yii\helpers\Html;

?>
<div class="order-documents">
    <div class="row row-bottom-margin">
        <div class="col-sm-2">
            <label><?= Yii::t('app', 'Document') ?>: </label>
        </div>
        <div class="col-sm-7">
            <?= Html::dropDownList("template_id", null, [], ['class' => 'form-control']); ?>
        </div>
        <div class="col-sm-3">
            <?= Html::button(Yii::t('app', 'Generate'), ['class' => 'js-generate-doc btn btn-primary pull-right']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Сформирован</th>
                    <th>Сформировал</th>
                    <th>Тип документа</th>
                    <th>Ссылка</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            </table>
        </div>
    </div>
</div>