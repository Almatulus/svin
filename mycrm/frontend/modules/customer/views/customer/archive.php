<?php

use core\models\customer\CompanyCustomer;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel frontend\search\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $form yii\widgets\ActiveForm */
/* @var $fetchedCustomers integer */


$this->title            = Yii::t('app', 'Customers archive');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_customers"></div>{link}</li>',
    'label'    => $this->title,
];
$this->params['bodyID'] = 'customers';
?>

<div class="customer-index">

    <?php
    echo Html::hiddenInput('js-customers-count',
        $dataProvider->getTotalCount());
    ?>
    <div class="column_row row buttons-row">
        <div class="col-sm-7 col-xs-12 input-with-select-sm">
            <?php $form = ActiveForm::begin([
                'action'  => ['archive'],
                'method'  => 'get',
                'id'      => 'js-activeform',
            ]); ?>
            <?= $this->render('_search_contact',
                ['model' => $searchModel, 'form' => $form]) ?>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col-sm-5 col-xs-12 right-buttons">
        </div>
    </div>
    <div id="js-customers-gridview">
        <?php
            $gridColumns = [
                'id',
                [
                    'attribute' => 'name',
                    'format'    => 'html',
                    'value'     => function (CompanyCustomer $model) {
                        $title = $model->customer->getFullName();
                        if (Yii::$app->user->can("companyCustomerView")) {
                            return Html::a($title, ['view', 'id' => $model->id]);
                        } else {
                            return $title;
                        }
                    }
                ],
                [
                    'attribute' => 'phone',
                    'label'     => Yii::t('app', 'Contacts'),
                    'format'    => 'html',
                    'value'     => function (CompanyCustomer $model) {
                        return $model->customer->phone . "<br>" . $model->customer->email;
                    }
                ],
                [
                    'format'    => 'date',
                    'attribute' => 'customer.birth_date',
                ],
                [
                    'attribute' => 'customer.iin',
                    'format'    => 'html',
                    'value'     => function (CompanyCustomer $model) {
                        return $model->customer->iin;
                    }
                ],
                [
                    'attribute' => 'customer.id_card_number',
                    'format'    => 'html',
                    'value'     => function (CompanyCustomer $model) {
                        return $model->customer->id_card_number;
                    }
                ],
                [
                    'attribute' => 'lastVisit',
                    'label'     => Yii::t('app', 'Last Visit Date'),
                    'format'    => 'html',
                    'value'     => function (CompanyCustomer $model) {
                        $lastVisitDatetime = $model->getLastVisitDateTime();
                        if ($lastVisitDatetime !== null) {
                            return Yii::$app->formatter->asDate($lastVisitDatetime)
                                . "<br>"
                                . $model->lastOrder->staff->getFullName();
                        }

                        return "";
                    }
                ],
                [
                    'attribute' => 'moneySpent',
                    'format'    => 'decimal',
                    'hAlign'    => 'right',
                    'value'     => function (CompanyCustomer $model) {
                        return $model->revenue;
                    }
                ],
                [
                    'attribute' => 'discount',
                    'value'     => function (CompanyCustomer $model) {
                        return $model->discount . ' %';
                    },
                    'hAlign'    => 'right',
                ],
                [
                    'label' => Yii::t('app', 'Gender'),
                    'value' => function (CompanyCustomer $model) {
                        return $model->customer->getGenderName();
                    }
                ],
                [
                    'class'    => 'yii\grid\ActionColumn',
                    'buttons'  => [
                        'restore' => function ($url, $model, $key) {
                            return Html::a(Yii::t('app', 'Restore'),
                                ['restore', 'id' => $model->id],
                                [
                                    'class'        => 'btn btn-default',
                                    'data-confirm' => Yii::t('app', 'Are you sure you want to restore this customer?'),
                                    'data-method'  => 'post',
                                ]
                            );
                        }
                    ],
                    'template' => '{restore}'
                ],
            ];

            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'id'           => 'customers',
                'options'      => [
                    'class' => 'customers-table',
                ],
                'toolbar'      => [
                    '{export}',
                    '{toggleData}'
                ],

                'columns'        => $gridColumns,
                'responsiveWrap' => false,
                'summary'        => \core\helpers\HtmlHelper::getSummary(),
                'pager'          => [
                    'firstPageLabel' => Yii::t('app', 'First'),
                    'lastPageLabel'  => Yii::t('app', 'Last'),
                    'pageCssClass'   => 'js-customers-search'
                ],
            ]);

        ?>
    </div>
</div>