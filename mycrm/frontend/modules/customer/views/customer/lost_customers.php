<?php

use core\helpers\HtmlHelper as Html;
use core\models\customer\CompanyCustomer;
use core\models\ServiceCategory;
use core\models\Staff;
use core\models\customer\CustomerCategory;
use core\models\division\Division;
use core\models\division\DivisionService;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model CompanyCustomer */

$this->title = Yii::t('app', 'Lost Customers');

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label' => Yii::t('app', 'Customers'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="customer-index">

<?php
    echo Html::hiddenInput('js-fetched-customers', json_encode(ArrayHelper::getColumn($dataProvider->getModels(), 'id')));
    echo Html::hiddenInput('js-customers-count', $dataProvider->getTotalCount());
?>

    <?php
    $form = ActiveForm::begin([
        'id' => 'js-activeform',
        'method' => 'get',
        'options' => ['data-pjax' => true ],
    ]);
    ?>

    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($model, 'numberOfDays'); ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'service_category')->dropDownList(ServiceCategory::map(), [
                'prompt' => Yii::t('app', 'Undefined')
            ]); ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($model, 'service')->dropDownList(DivisionService::getOwnCompanyDivisionServicesList(), [
                'prompt' => Yii::t('app', 'Undefined')
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'category')->dropDownList(CustomerCategory::map(), [
                'prompt' => Yii::t('app', 'Undefined')
            ]); ?>
        </div>
        <div class="col-sm-3">
            <?= $form->field($model, 'division')->dropDownList(Division::getOwnDivisionsNameList(), [
                'prompt' => Yii::t('app', 'Undefined')
            ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <button type="submit" class="btn btn-primary"><?= Yii::t('app', 'Find') ?></button>
            <?= Html::a(Yii::t('app', 'Export'), 'lost-export?' . Yii::$app->request->queryString, ['class' => 'btn btn-default js-export-report']) ?>
            <div class="customer-actions pull-right">
                <div class="dropdown inline_block">
                    <button class="btn btn_dropdown" data-toggle="dropdown" aria-expanded="false">
                        Действия <b class="caret"></b>
                    </button>
                    <ul class="dropdown-menu" style="margin-left: -150px">
                        <li>
                            <?= Html::a('<i class="fa fa-envelope"></i> ' . Yii::t('app', 'Send SMS selected'), '#', ['class' => 'js-button-request js-selected disabled']) ?>
                        </li>
                        <li>
                            <?= Html::a('<i class="fa fa-envelope"></i> ' . Yii::t('app', 'Send SMS fetched'), '#', ['class' => 'js-button-request js-fetched']) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php $form->end(); ?>

    <div class="row">
        <div class="col-sm-12">
            <div id="js-customers-gridview">
                <?= GridView::widget([
                    'id' => 'customers',
                    'options' => [
                        'class' => 'customers-table',
                    ],
                    'dataProvider' => $dataProvider,
                    'pager' => [
                        'pageCssClass' => 'js-customers-search'
                    ],
                    'summary' => Html::getSummary(),
                    'columns' => [
                        ['class' => 'yii\grid\CheckboxColumn'],
                        [
                            'format' => 'html',
                            'attribute' => 'customer.fullName',
                            'value' => function(CompanyCustomer $model) {
                                return Html::a($model->customer->getFullName(), ['/customer/customer/view', 'id' => $model->id]);
                            }
                        ],
                        'customer.phone',
                        [
                            'label' => Yii::t('app', 'Last Visit Date'),
                            'format' => 'datetime',
                            'value' => 'lastOrder.datetime',
                        ],
                        [
                            'label' => Yii::t('app', 'Staff ID'),
                            'value' => 'lastOrder.staff.fullName',
                        ],
                        [
                            'label' => Yii::t('app', 'Division ID'),
                            'value' => 'lastOrder.division.name',
                        ],
                        [
                            'label' => Yii::t('app', 'Money spent'),
                            'format' => 'decimal',
                            'value' => 'lastOrder.income',
                        ],
                    ]
                ])
                ?>
            </div>
        </div>
    </div>
</div>

<?= $this->render('modals/_request') ?>

