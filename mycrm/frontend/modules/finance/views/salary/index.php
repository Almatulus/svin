<?php

use core\helpers\HtmlHelper as Html;
use core\models\StaffPayment;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel \frontend\search\StaffPaymentSearch */

$this->title = Yii::t('app', 'Salary Report');

$this->params['breadcrumbs'][] = ['template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>', 'label' => $this->title, 'url' => ['index']];
?>
<div class="finance-payment">
    <div class="finance-payment-filters">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
        ]); ?>
        <div class="row details-row">
            <div class="col-md-3">
                <?=
                $form->field($searchModel, 'start_date', [
                    'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app', 'From date') . '</span>{input}</div>',
                ])->widget(DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd'
                    ]
                ]); ?>
            </div>
            <div class="col-md-3">
                <?=
                $form->field($searchModel, 'end_date', [
                    'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app', 'To date') . '</span>{input}</div>',
                ])->widget(DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd'
                    ]
                ])->label('По'); ?>
            </div>
            <div class="col-md-3">
                <?=
                $form->field($searchModel, 'order_date')->widget(DatePicker::className(), [
                    'type'          => DatePicker::TYPE_INPUT,
                    'options'       => ['placeholder' => 'Дата записи'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format'    => 'yyyy-mm-dd'
                    ]
                ])->label(false); ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'staff_id')->widget(Select2::className(), [
                    'data' => \core\models\Staff::getOwnCompanyStaffList(),
                    'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Staff')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'language' => 'ru'
                    ],
                    'size' => 'sm',
                    'showToggleAll' => false,
                    'theme' => Select2::THEME_CLASSIC,
                ])->label(false); ?>
            </div>
            <div class="col-md-3">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?php $form->end() ?>
    </div>

    <?= \yii\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => Html::getSummary(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'payment_date:date',
            [
                'attribute' => 'staff.name',
                'label' => 'Имя сотрудника'
            ],
            [
                'attribute' => 'staff.phone',
                'label' => 'Телефон сотрудника'
            ],
            'start_date:date',
            'end_date:date',
            'salary:currency',
            [
                'class'          => 'yii\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => function (StaffPayment $model, $key, $index) {
                        return $model->isEditable();
                    },
                    'clear' => function (StaffPayment $model, $key, $index) {
                        return !$model->isEditable();
                    },
                    'view'   => function (StaffPayment $model, $key, $index) {
                        return $model->isEditable();
                    }
                ],
                'template' => '{view} {delete} {clear}',
                'buttons' => [
                    'clear' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>',
                            ['/finance/salary/clear', 'id' => $model->id],
                            [
                                'title' => Yii::t('app', 'Clear'),
                                'data' => [
                                    'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ]
                            ]
                        );
                    }
                ],
            ]
        ],
    ]); ?>
</div>