<?php
/**
 * @var Company $company
 * @var array   $abbreviations
 * @var array   $diagnoses
 */

use core\models\company\Company;
use kartik\popover\PopoverX;
use yii\helpers\Html;

$formName = isset($formName) ? $formName : 'MedCard[teeth]';
?>
<div class="order-tooth teeth-form details-row"
     data-colors='<?= json_encode($colors) ?>'
     data-abbreviations='<?= json_encode($abbreviations) ?>'>
    <div class="order-tooth-controls">
        <a class="show-child-tooth" href="javascript:void(0);"
           data-title-on="Включить детские зубы"
           data-title-off="Выключить детские зубы" data-enabled="0"
           data-container='teeth-form'>
            Включить детские зубы
        </a>
    </div>
    <div class="row">
        <div class="col-sm-6 text-right">
            <?php for ($i = 18; $i >= 11; $i--) { ?>
                <div class="order-tooth-wrapper order-tooth-wrapper-<?= $i ?>">
                    <span class="tooth-number"><?= $i ?></span>
                    <?= PopoverX::widget([
                        'header'       => Yii::t('app', 'Select diagnosis'),
                        'placement'    => PopoverX::ALIGN_RIGHT,
                        'content'      =>
                            Html::dropdownList(
                                "{$formName}[{$i}][diagnosis_id]",
                                null,
                                $diagnoses,
                                [
                                    'prompt'     =>
                                        Yii::t('app', 'Select diagnosis'),
                                    'class'      => 'js-select-tooth-diagnosis form-control',
                                    'data-tooth' => $i,
                                    'style'      => 'margin-bottom: 4px'
                                ]
                            )
                            . Html::textInput(
                                "{$formName}[{$i}][mobility]",
                                null,
                                [
                                    'hidden'      => true,
                                    'class'       => 'tooth-mobility form-control',
                                    'data-tooth'  => $i,
                                    'placeholder' => 'Подвижность'
                                ]
                            ),
                        'toggleButton' => [
                            'tag'   => 'div',
                            'class' => "order-tooth-img order-tooth-img-{$i}",
                            'label' => Html::img(
                                '\image\teeth.png',
                                ['height' => '35']
                            ),
                        ]
                    ]) ?>
                </div>
            <?php } ?>
        </div>
        <div class="col-sm-6">
            <?php for ($i = 21; $i <= 28; $i++) { ?>
                <div class="order-tooth-wrapper order-tooth-wrapper-<?= $i ?>">
                    <span class="tooth-number"><?= $i ?></span>
                    <?= PopoverX::widget([
                        'header'       => Yii::t('app', 'Select diagnosis'),
                        'placement'    => PopoverX::ALIGN_RIGHT,
                        'content'      =>
                            Html::dropdownList(
                                "{$formName}[{$i}][diagnosis_id]",
                                null,
                                $diagnoses,
                                [
                                    'prompt'     =>
                                        Yii::t('app', 'Select diagnosis'),
                                    'class'      => 'js-select-tooth-diagnosis form-control',
                                    'data-tooth' => $i,
                                    'style'      => 'margin-bottom: 4px'
                                ]
                            )
                            . Html::textInput(
                                "{$formName}[{$i}][mobility]",
                                null,
                                [
                                    'hidden'      => true,
                                    'class'       => 'tooth-mobility form-control',
                                    'data-tooth'  => $i,
                                    'placeholder' => 'Подвижность'
                                ]
                            ),
                        'toggleButton' => [
                            'tag'   => 'div',
                            'class' => "order-tooth-img order-tooth-img-{$i}",
                            'label' => Html::img(
                                '\image\teeth.png',
                                ['height' => '35']
                            ),
                        ]
                    ]) ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="row child-tooth-row" hidden>
        <div class="col-sm-6 text-right">
            <?php for ($i = 55; $i >= 51; $i--) { ?>
                <div class="order-tooth-wrapper order-tooth-wrapper-<?= $i ?>">
                    <span class="tooth-number"><?= $i ?></span>
                    <?= PopoverX::widget([
                        'header'       => Yii::t('app', 'Select diagnosis'),
                        'placement'    => PopoverX::ALIGN_RIGHT,
                        'content'      =>
                            Html::dropdownList(
                                "{$formName}[{$i}][diagnosis_id]",
                                null,
                                $diagnoses,
                                [
                                    'prompt'     =>
                                        Yii::t('app', 'Select diagnosis'),
                                    'class'      => 'js-select-tooth-diagnosis form-control',
                                    'data-tooth' => $i,
                                    'style'      => 'margin-bottom: 4px'
                                ]
                            )
                            . Html::textInput(
                                "{$formName}[{$i}][mobility]",
                                null,
                                [
                                    'hidden'      => true,
                                    'class'       => 'tooth-mobility form-control',
                                    'data-tooth'  => $i,
                                    'placeholder' => 'Подвижность'
                                ]
                            ),
                        'toggleButton' => [
                            'tag'   => 'div',
                            'class' => "order-tooth-img order-tooth-img-{$i}",
                            'label' => Html::img(
                                '\image\teeth.png',
                                ['height' => '35']
                            ),
                        ]
                    ]) ?>
                </div>
            <?php } ?>
        </div>
        <div class="col-sm-6">
            <?php for ($i = 61; $i <= 65; $i++) { ?>
                <div class="order-tooth-wrapper order-tooth-wrapper-<?= $i ?>">
                    <span class="tooth-number"><?= $i ?></span>
                    <?= PopoverX::widget([
                        'header'       => Yii::t('app', 'Select diagnosis'),
                        'placement'    => PopoverX::ALIGN_RIGHT,
                        'content'      =>
                            Html::dropdownList(
                                "{$formName}[{$i}][diagnosis_id]",
                                null,
                                $diagnoses,
                                [
                                    'prompt'     =>
                                        Yii::t('app', 'Select diagnosis'),
                                    'class'      => 'js-select-tooth-diagnosis form-control',
                                    'data-tooth' => $i,
                                    'style'      => 'margin-bottom: 4px'
                                ]
                            )
                            . Html::textInput(
                                "{$formName}[{$i}][mobility]",
                                null,
                                [
                                    'hidden'      => true,
                                    'class'       => 'tooth-mobility form-control',
                                    'data-tooth'  => $i,
                                    'placeholder' => 'Подвижность'
                                ]
                            ),
                        'toggleButton' => [
                            'tag'   => 'div',
                            'class' => "order-tooth-img order-tooth-img-{$i}",
                            'label' => Html::img(
                                '\image\teeth.png',
                                ['height' => '35']
                            ),
                        ]
                    ]) ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 text-right">
            <?php for ($i = 48; $i >= 41; $i--) { ?>
                <div class="order-tooth-wrapper order-tooth-wrapper-<?= $i ?>">
                    <?= PopoverX::widget([
                        'header'       => Yii::t('app', 'Select diagnosis'),
                        'placement'    => PopoverX::ALIGN_RIGHT,
                        'content'      =>
                            Html::dropdownList(
                                "{$formName}[{$i}][diagnosis_id]",
                                null,
                                $diagnoses,
                                [
                                    'prompt'     =>
                                        Yii::t('app', 'Select diagnosis'),
                                    'class'      => 'js-select-tooth-diagnosis form-control',
                                    'data-tooth' => $i,
                                    'style'      => 'margin-bottom: 4px'
                                ]
                            )
                            . Html::textInput(
                                "{$formName}[{$i}][mobility]",
                                null,
                                [
                                    'hidden'      => true,
                                    'class'       => 'tooth-mobility form-control',
                                    'data-tooth'  => $i,
                                    'placeholder' => 'Подвижность'
                                ]
                            ),
                        'toggleButton' => [
                            'tag'   => 'div',
                            'class' => "order-tooth-img order-tooth-img-{$i}",
                            'label' => Html::img(
                                '\image\teeth.png',
                                ['height' => '35']
                            ),
                        ]
                    ]) ?>
                    <span class="tooth-number"><?= $i ?></span>
                </div>
            <?php } ?>
        </div>
        <div class="col-sm-6">
            <?php for ($i = 31; $i <= 38; $i++) { ?>
                <div class="order-tooth-wrapper order-tooth-wrapper-<?= $i ?>">
                    <?= PopoverX::widget([
                        'header'       => Yii::t('app', 'Select diagnosis'),
                        'placement'    => PopoverX::ALIGN_RIGHT,
                        'content'      =>
                            Html::dropdownList(
                                "{$formName}[{$i}][diagnosis_id]",
                                null,
                                $diagnoses,
                                [
                                    'prompt'     =>
                                        Yii::t('app', 'Select diagnosis'),
                                    'class'      => 'js-select-tooth-diagnosis form-control',
                                    'data-tooth' => $i,
                                    'style'      => 'margin-bottom: 4px'
                                ]
                            )
                            . Html::textInput(
                                "{$formName}[{$i}][mobility]",
                                null,
                                [
                                    'hidden'      => true,
                                    'class'       => 'tooth-mobility form-control',
                                    'data-tooth'  => $i,
                                    'placeholder' => 'Подвижность'
                                ]
                            ),
                        'toggleButton' => [
                            'tag'   => 'div',
                            'class' => "order-tooth-img order-tooth-img-{$i}",
                            'label' => Html::img(
                                '\image\teeth.png',
                                ['height' => '35']
                            ),
                        ]
                    ]) ?>
                    <span class="tooth-number"><?= $i ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="row child-tooth-row" hidden>
        <div class="col-sm-6 text-right">
            <?php for ($i = 85; $i >= 81; $i--) { ?>
                <div class="order-tooth-wrapper order-tooth-wrapper-<?= $i ?>">
                    <?= PopoverX::widget([
                        'header'       => Yii::t('app', 'Select diagnosis'),
                        'placement'    => PopoverX::ALIGN_RIGHT,
                        'content'      =>
                            Html::dropdownList(
                                "{$formName}[{$i}][diagnosis_id]",
                                null,
                                $diagnoses,
                                [
                                    'prompt'     =>
                                        Yii::t('app', 'Select diagnosis'),
                                    'class'      => 'js-select-tooth-diagnosis form-control',
                                    'data-tooth' => $i,
                                    'style'      => 'margin-bottom: 4px'
                                ]
                            )
                            . Html::textInput(
                                "{$formName}[{$i}][mobility]",
                                null,
                                [
                                    'hidden'      => true,
                                    'class'       => 'tooth-mobility form-control',
                                    'data-tooth'  => $i,
                                    'placeholder' => 'Подвижность'
                                ]
                            ),
                        'toggleButton' => [
                            'tag'   => 'div',
                            'class' => "order-tooth-img order-tooth-img-{$i}",
                            'label' => Html::img(
                                '\image\teeth.png',
                                ['height' => '35']
                            ),
                        ]
                    ]) ?>
                    <span class="tooth-number"><?= $i ?></span>
                </div>
            <?php } ?>
        </div>
        <div class="col-sm-6">
            <?php for ($i = 71; $i <= 75; $i++) { ?>
                <div class="order-tooth-wrapper order-tooth-wrapper-<?= $i ?>">
                    <?= PopoverX::widget([
                        'header'       => Yii::t('app', 'Select diagnosis'),
                        'placement'    => PopoverX::ALIGN_RIGHT,
                        'content'      =>
                            Html::dropdownList(
                                "{$formName}[{$i}][diagnosis_id]",
                                null,
                                $diagnoses,
                                [
                                    'prompt'     =>
                                        Yii::t('app', 'Select diagnosis'),
                                    'class'      => 'js-select-tooth-diagnosis form-control',
                                    'data-tooth' => $i,
                                    'style'      => 'margin-bottom: 4px'
                                ]
                            )
                            . Html::textInput(
                                "{$formName}[{$i}][mobility]",
                                null,
                                [
                                    'hidden'      => true,
                                    'class'       => 'tooth-mobility form-control',
                                    'data-tooth'  => $i,
                                    'placeholder' => 'Подвижность'
                                ]
                            ),
                        'toggleButton' => [
                            'tag'   => 'div',
                            'class' => "order-tooth-img order-tooth-img-{$i}",
                            'label' => Html::img(
                                '\image\teeth.png',
                                ['height' => '35']
                            ),
                        ]
                    ]) ?>
                    <span class="tooth-number"><?= $i ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<hr>