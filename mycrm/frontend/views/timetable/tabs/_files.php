<?php

use kartik\file\FileInput;
use yii\helpers\Url;
use yii\web\JsExpression;

?>
<div class="details-row">
    <?php
    if (isset($showFileInput) && $showFileInput) {
        echo FileInput::widget([
            'name' => 'file',
            'pluginOptions' => [
                'showCancel' => false,
                'showPreview' => false,
                'showRemove' => false,
                'uploadUrl' => Url::to(['/timetable/upload-file']),
                'uploadExtraData' => new JsExpression("function(previewId, index) {
                    return {id : $('#order-id').val()};
                }")
            ],
            'pluginEvents' => [
                'fileuploaded' => new JsExpression("function(event, data, previewId, index) {
                    $.jGrowl('Файл успешно добавлен на сервер', { group: 'flash_notice'});
                    renderFile(data.response);
                    $('#files input[name=file]').fileinput('clear').fileinput('enable');
                }"),
                'fileuploaderror' => new JsExpression("function(event, data, msg) {
                    alertMessage(msg);
                    $('#files input[name=file]').fileinput('refresh').fileinput('enable');
                }")
            ]
        ]);
    }
    ?>
</div>

<div class="order-files row details-row">
    <?php
        if (!empty($files)) {
            foreach ($files as $key => $file) {
                $src = $file->path;
                $filename = pathinfo($file->path)['filename'];
                if (!preg_match('/(\.jpg|\.png|\.bmp)$/i', $src)) {
                    $src = '/image/download.png';
                }
    ?>
        <div class="col-sm-4">
            <figure class="img-thumbnail row-col">
                <img src="<?= $src ?>" class="img img-responsive">
                <figcaption class="text-center">
                    <span><?= $filename ?></span>
                    <a href="/timetable/delete-file?id=<?= $file->id; ?>" class="js-delete-file pull-right">
                        <span class="glyphicon glyphicon-trash"></span>
                    </a>
                    <a href="" target="_blank" class="pull-right">
                        <span class="glyphicon glyphicon-download"></span>
                    </a>
            </figure>
        </div>
    <?php
            }
        }
    ?>
</div>