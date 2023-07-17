<?php

use core\models\Image;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model core\models\customer\CompanyCustomer */
/* @var $files \core\models\File[] */
/* @var $orders_passed core\models\order\Order[] */
/* @var $orders_soon core\models\order\Order[] */

$this->title                      = $model->customer->getFullName();
$this->params['breadcrumbs'][]    = ['template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>', 'label' => Yii::t('app', 'Customers'), 'url' => ['index']];
$this->params['breadcrumbs'][]    = $this->title;
$this->params['mainContentClass'] = 'customers';
?>
    <div class="customer-view">

        <div class="column_row buttons-row">
            <div class="right-buttons">
                <?php if ($model->balance < 0) {
                    echo Html::a(
                        Yii::t('app', 'Pay the debt'),
                        ['/customer/customer/pay-debt', 'id' => $model->id],
                        [
                            'class'       => 'btn btn-default js-pay-debt-button',
                            'data-debt'   => Yii::$app->formatter->asDecimal(abs($model->getDebt())),
                            'data-reload' => 1
                        ]
                    );
                } ?>
                <?php if (Yii::$app->user->can("companyCustomerUpdate", ['model' => $model]) &&
                          Yii::$app->user->identity->canSeeCustomerPhones()): ?>
                    <?= Html::a('редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="column_row">
            <div class="row details-row">
                <div class="col-sm-3">
                    <img src="<?= $model->customer->getAvatarImageUrl() ?>" height="<?= Image::SIZE_AVATAR?>" class="img-circle">
                </div>
                <div class="col-sm-9">
                    <div class="row-col col-sm-6">
                        <span class="lbl">Телефон: </span>
                        <?= $model->customer->phone ?: Yii::t('app', 'Undefined'); ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">Добавлен: </span>
                        <?= Yii::$app->formatter->asDate($model->created_time) ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">Email: </span>
                        <?= $model->customer->email ?: Yii::t('app', 'Undefined'); ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">Пол: </span>
                        <?= $model->customer->getGenderName() ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">Возраст: </span>
                        <?php
                        $age = $model->customer->getAge();
                        if ($age !== null):
                            echo Yii::t('app', '{n} лет', ['n' => $age])
                            ?>
                            (<?= Yii::$app->formatter->asDate($model->customer->birth_date) ?>)
                        <?php else: ?>
                            <?= Yii::t('app', 'Unknown'); ?>
                        <?php endif; ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">Адрес: </span>
                        <?php if ( ! empty($model->address)): ?>
                            <?php if ( ! empty($model->city)): ?>
                                г.<?= $model->city ?>,
                            <?php endif; ?>
                            <?php if ( ! empty($model->address)): ?>
                                <?= $model->address ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?= Yii::t('app', 'Unknown'); ?>
                        <?php endif; ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">
                            <?= Yii::t('app', 'Deposit') ?>:
                        </span>
                        <?= Yii::$app->formatter->asDecimal($model->getDeposit()) ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">Категория: </span>
                        <?php if (!empty($model->categories)):
                            echo implode(', ', ArrayHelper::getColumn($model->categories, 'name'));
                        else:
                            echo Yii::t('app', 'Unknown');
                        endif; ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">
                            <?= Yii::t('app', 'Debt') ?>:
                        </span>
                        <?= Yii::$app->formatter->asDecimal($model->getDebt()) ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">
                            <?= Yii::t('app', 'Cashback Percent') ?>:
                        </span>
                        <?= $model->cashback_percent . " %" ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">
                            <?= Yii::t('app', 'Cashback') ?>:
                        </span>
                        <?= Yii::$app->formatter->asDecimal($model->cashback_balance) ?>
                    </div>
                    <div class="row-col col-sm-6">
                        <span class="lbl">
                            <?= Yii::t('app', 'Number of medical record') ?>:
                        </span>
                        <?= $model->medical_record_id ?: Yii::t('app', 'Undefined'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="single-detail-row">
            <div class="detail-content">
                <div class="detail-text" id="description_text" style="display: block;">
                    <div class="value">
                        <p>Описание: <?= $model->comments ?: Yii::t('app', 'Unknown') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs">
            <li class="active tab"><a href="#history" data-toggle="tab">Визиты</a></li>
            <li class="tab"><a href="#documents" data-toggle="tab">Документы</a></li>
            <li class="tab"><a href="#medexams" data-toggle="tab">Лечение</a></li>
            <li class="tab"><a href="#files" data-toggle="tab">Файлы</a></li>
        </ul>

        <br>

        <div class="tab-content">
            <div class="tab-pane active" id="history">
                <?= $this->render('tabs/_history', compact('orders_passed', 'orders_soon')); ?>
            </div>
            <div class="tab-pane" id="documents">
                <?= $this->render('tabs/_documents', compact('documents')); ?>
            </div>
            <div class="tab-pane" id="medexams">
                <?= $this->render('//timetable/tabs/_med_exams'); ?>
            </div>
            <div class="tab-pane" id="files">
                <?= $this->render('//timetable/tabs/_files', ['files' => $files, 'showFileInput' => false]); ?>
            </div>
        </div>

    </div>

<?= $this->render('//timetable/modal/_med_card'); ?>

<?php


$this->registerJs(
    "var company_phone = '{$model->company->phone}'",
    View::POS_END
);
$this->registerJs(<<<JS
    loadMedCards({$model->id});
    addFilesListener();
JS
);
