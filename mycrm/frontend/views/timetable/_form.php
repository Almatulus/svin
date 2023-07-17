<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model core\models\order\Order */
/* @var $form yii\widgets\ActiveForm */
/* @var $staff \core\models\Staff */
?>
<div class="order-form">
    <div class="row">

        <!-- Navigation Buttons -->
        <div class="col-md-3 left-side">
            <ul class="nav nav-pills" id="orderTabs">
                <li id="info_tab" class="active">
                    <a href="#info" data-toggle="pill">Информация</a>
                </li>
                <li id="customer_tab" class="tab">
                    <a href="#customer" data-toggle="pill">
                        Данные клиента</a>
                </li>
                <?php if (Yii::$app->user->identity->company->canManageMedicalCard()): ?>
                    <li id="tooth_tab" class="tab">
                        <a href="#tooth" data-toggle="pill">Лечение</a>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->identity->company->canViewFiles()): ?>
                    <li id="files_tab" class="tab">
                        <a href="#files" data-toggle="pill">Файлы</a>
                    </li>
                <?php endif; ?>
                <li id="docs_tab" class="tab">
                    <a href="#docs" data-toggle="pill">Документы</a>
                </li>
                <li class="divider">
                    <hr>
                </li>
                <li id="history_tab" class="tab">
                    <a href="#history" data-toggle="pill">История изменений</a>
                </li>
                <li id="visits_tab" class="tab">
                    <a href="#visits" data-toggle="pill">История посещений</a>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="col-md-9 right-side">
            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <?php $form = ActiveForm::begin([
                        'id'          => 'order-form',
                        'fieldConfig' => [
                            'options' => ['class' => ''],
                        ]
                    ]); ?>
                    <?= $this->render('tabs\_info', [
                        'form'  => $form,
                        'model' => $model,
                        'staff' => $staff
                    ]) ?>
                    <?php ActiveForm::end(); ?>
                </div>
                <div class="tab-pane" id="customer">
                    <?= $this->render('tabs\_customer') ?>
                </div>
                <?php if (Yii::$app->user->identity->company->canManageMedicalCard()): ?>
                    <div class="tab-pane" id="tooth">
                        <?= $this->render('tabs\_med_exams'); ?>
                    </div>
                <?php endif; ?>
                <?php if (Yii::$app->user->identity->company->canViewFiles()) { ?>
                    <div class="tab-pane" id="files">
                        <?= $this->render('tabs\_files',
                            ['showFileInput' => true]); ?>
                    </div>
                <?php } ?>
                <div class="tab-pane" id="docs">
                    <?= $this->render('tabs\_documents'); ?>
                </div>
                <div class="tab-pane" id="history">
                    <?= $this->render('tabs\_history'); ?>
                </div>
                <div class="tab-pane" id="visits"></div>
            </div>
        </div>
    </div>
</div>
