<?php
use core\models\finance\CompanyCashflow;
use kartik\grid\GridView;

?>
    <div class="column_row row buttons-row">
        <div class="col-sm-8">
        </div>
        <div class="col-sm-4 right-buttons">
        </div>
    </div>
    <div class="row column_row">
        <div class="col-sm-12">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'date:date',
                    [
                        'attribute' => 'cost_item_id',
                        'value' => function (CompanyCashflow $cashflow) {
                            return $cashflow->costItem->getFullName();
                        }
                    ],
                    [
                        'attribute' => 'cash_id',
                        'value' => function (CompanyCashflow $cashflow) {
                            return $cashflow->cash->name;
                        }
                    ],
                    [
                        'attribute' => 'contractor_id',
                        'value' => function (CompanyCashflow $cashflow) {
                            return $cashflow->contractor ? $cashflow->contractor->name : null;
                        }
                    ],
                    [
                        'attribute' => 'value',
                        'value' => function (CompanyCashflow $cashflow) {
                            return $cashflow->value . ' ' . Yii::t('app', 'Currency');
                        }
                    ],
                    'comment:ntext',
                ],
            ]); ?>
        </div>
    </div>