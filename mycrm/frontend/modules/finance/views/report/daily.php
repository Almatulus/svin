<?php

/** @var $this \yii\web\View */
/* @var $models \core\models\finance\CompanyCashflow[][][] */
/* @var $model \core\forms\finance\DailyReportForm */
/* @var $paymentsList array */

/* @var $yesterdayBalance array */

use core\forms\finance\DailyReportForm;
use core\models\division\Division;
use core\models\division\DivisionService;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCostItem;
use core\models\ServiceCategory;
use core\models\Staff;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Staff report');
$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label'    => $this->title
];
$this->params['bodyID'] = 'finance';
?>

<?php $form = ActiveForm::begin([
    'action'      => ['daily'],
    'fieldConfig' => ['template' => "{input}\n{hint}\n{error}"],
    'method'      => 'get',
]); ?>
    <div class="row">
        <div class="col-md-12">
            <h3><?= Yii::t('app', 'Filters') ?></h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            Группировать по:<br>
            <?= $form->field($model, 'groupBy')->dropDownList(DailyReportForm::getGroupByList()) ?>
        </div>
        <div class="col-md-3">
            Отобразить:<br>
            <button class="btn btn_dropdown" type="button" data-toggle="collapse" data-target="#collapse1"
                    aria-expanded="false" aria-controls="collapse1">
                Список столбцов <b class="caret"></b>
            </button>
            <div style="position: absolute; background-color: white; z-index:10">
                <div class="collapse" id="collapse1">
                    <table>
                        <tr>
                            <td>
                                <?= Html::checkbox("toggleAllColumns",
                                    sizeof(array_filter($model->getVisibleColumns())) == sizeof($model->getColumnLabels()),
                                    ['label' => 'Выбрать все', 'class' => 'js-toggle-all-columns']) ?>
                            </td>
                        </tr>
                        <?php foreach ($model->getColumnLabels() as $columnKey => $columnLabel): ?>
                            <tr>
                                <?= $form->field($model, "visibleColumns[{$columnKey}]", [
                                    'template' => '<td>{input}</td>'
                                ])->checkbox(['label' => $columnLabel, 'class' => 'js-visible-column']) ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            Отобразить:
            <?= $form->field($model, 'total_only')->checkbox(['label' => Yii::t('app', 'Total Only')]) ?>
        </div>
    </div>
    <div class="row details-row">

        <div class="col-md-3">
            <?=
            $form->field($model, 'start', [
                'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                        'From date') . '</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]); ?>
        </div>
        <div class="col-md-3">
            <?=
            $form->field($model, 'end', [
                'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app',
                        'To date') . '</span>{input}</div>',
            ])->widget(DatePicker::className(), [
                'type'          => DatePicker::TYPE_INPUT,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'yyyy-mm-dd'
                ]
            ]); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'division')->dropDownList(Division::getOwnDivisionsNameList(), [
                'prompt' => Yii::t('app', 'All Divisions')
            ]); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'staff')->widget(Select2::class, [
                'data'          => Staff::getOwnCompanyStaffList(),
                'options'       => ['placeholder' => Yii::t('app', 'All Staff')],
                'pluginOptions' => ['allowClear' => true],
                'size'          => 'sm'
            ]); ?>
        </div>
        <div class="col-md-3">
            <?php $cashes = CompanyCash::map() ?>
            <?= $form->field($model, 'cash')->dropDownList($cashes, [
                'prompt' => Yii::t('app', 'All Cashes')
            ]); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'cost_item')->dropDownList(CompanyCostItem::map(null, [
                CompanyCostItem::COST_ITEM_TYPE_SERVICE,
                CompanyCostItem::COST_ITEM_TYPE_PRODUCT_SALE,
                CompanyCostItem::COST_ITEM_TYPE_DEBT_PAYMENT,
                CompanyCostItem::COST_ITEM_TYPE_REFUND
            ]), [
                'prompt' => Yii::t('app', 'All Cost Items')
            ]); ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'insurance_company')->widget(\kartik\select2\Select2::class, [
                'data'          => \core\models\InsuranceCompany::map(true),
                'options'       => ['placeholder' => Yii::t('app', 'All Insurance Companies')],
                'pluginOptions' => ['allowClear' => true],
                'size'          => 'sm'
            ]); ?>
        </div>
        <div class="col-md-3">
            <?php $insurers = \core\models\customer\CompanyCustomer::find()->select('insurer')
                ->company()
                ->active(true)
                ->andWhere("insurer IS NOT NULL AND insurer <> ''")
                ->orderBy('insurer ASC')
                ->indexBy('insurer')
                ->column();
            ?>
            <?= $form->field($model, 'insurer')->widget(\kartik\select2\Select2::class, [
                'data'          => $insurers,
                'options'       => ['placeholder' => Yii::t('app', 'All insurers')],
                'pluginOptions' => ['allowClear' => true],
                'size'          => 'sm'
            ]); ?>
        </div>

        <div class="col-md-3">
            <?php
            $data = $model->division_service ? DivisionService::find()->select([
                DivisionService::tableName() . '.id',
                DivisionService::tableName() . '.service_name as name'
            ])
                ->division($model->division, false)
                ->byId($model->division_service)
                ->asArray()
                ->all() : [];
            $data = ArrayHelper::map($data, "id", "name");
            ?>

            <?= $form->field($model, 'division_service')->widget(DepDrop::className(), [
                'type'           => DepDrop::TYPE_SELECT2,
                'data'           => $data,
                'pluginOptions'  => [
                    'depends'     => [Html::getInputId($model, 'division')],
                    'url'         => Url::to(['/division/service/list']),
                    'placeholder' => Yii::t('app', 'All services'),
                    'loadingText' => Yii::t('app', 'Loading...'),
                    'initialize'  => true,
                ],
                'pluginEvents'   => [
                    "depdrop:change" => "function(event, id, value, count) { $(this).change(); }",
                ],
                'options'        => ['placeholder' => Yii::t('app', 'All services')],
                'select2Options' => [
                    'size'          => Select2::SMALL,
                    'pluginOptions' => ['allowClear' => true]
                ]
            ]) ?>
        </div>

        <div class="col-md-3">
            <?php
            $data = $model->service_category ? ServiceCategory::find()->select([
                ServiceCategory::tableName() . '.id',
                ServiceCategory::tableName() . '.name'
            ])
                ->filterByDivision($model->division)
                ->byId($model->service_category)
                ->asArray()
                ->all() : [];
            $data = ArrayHelper::map($data, "id", "name");
            ?>

            <?= $form->field($model, 'service_category')->widget(DepDrop::className(), [
                'data'          => $data,
                'pluginOptions' => [
                    'depends'     => [Html::getInputId($model, 'division')],
                    'url'         => Url::to(['/service-category/list']),
                    'placeholder' => Yii::t('app', 'All Categories'),
                    'loadingText' => Yii::t('app', 'Loading...'),
                    'initialize'  => true,
                ],
                'options'       => ['placeholder' => Yii::t('app', 'All Categories')],
            ]) ?>
        </div>

        <div class="col-md-12 clearfix">
            <div class="pull-right">
                <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
                <?= Html::a(Yii::t('app', 'Export'), 'daily-export?' . Yii::$app->request->queryString,
                    ['class' => 'btn btn-default js-export-report']) ?>
            </div>
        </div>

    </div>
<?php $form->end() ?>

    <div class="row">
        <div class="col-md-12">
            <div class="data_table table-responsive">
                <table class="no_hover">
                    <?php
                    $manyCashes = sizeof($cashes) > 1;
                    $shift = $manyCashes ? 2 : 1;
                    $colspan = 6;
                    ?>
                    <thead>
                    <tr>
                        <?php
                        $payments = [];
                        ?>
                        <?php foreach ($model->columns as $column): ?>
                            <th class="<?= !$model->visibleColumns[$column] ? 'hidden' : '' ?>">
                                <?= DailyReportForm::getColumnLabels()[$column] ?>
                            </th>
                        <?php endforeach; ?>

                        <?php
                        foreach ($paymentsList as $id => $paymentName) {
                            echo "<th>" . Yii::t('app', $paymentName) . "</th>";
                            $payments[$id] = 0;
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $totalPaid = $totalDiscount = $totalDebt = 0;
                    $rowspans = [];
                    foreach ($models as $ind => $staffCashflows) {
                        $rowspans[$ind] = 0;
                        $staffPaid = $staffDiscount = $staffDebt = 0;
                        $cashRowSpan = sizeof($staffCashflows);
                        $staffPayments = array_fill_keys(array_keys($payments), 0);
                        $staff = null;

                        /* @var \core\models\finance\CompanyCashflow[] $staffCashflows*/
                        foreach ($staffCashflows as $orderCashflows) {
                            $ordRowspan = sizeof($orderCashflows) - 1;
                            $lastCashflow = end($orderCashflows);

                            foreach ($orderCashflows as $ordKey => $cashflow) {
                                /** @var \core\models\finance\CompanyCashflow $cashflow*/
                                $rowspan = 1;
                                $rowspans[$ind]++;
                                $staffPaid += $cashflow->getValue();
                                $cashflowDiscount = $cashflow->getDiscount();
                                $debt = $cashflow->order->payment_difference < 0 ? $cashflow->order->payment_difference : 0;
                                if ($ordKey == $ordRowspan) {
                                    $staffDebt += $debt;
                                    $staffDiscount += $cashflowDiscount;
                                }
                                $groupClass = "js-groupped-column-";
                                $staff = $cashflow->staff;
                                ?>
                                <tr>
                                    <?php foreach ($model->columns as $column): ?>
                                        <?php $class = !$model->visibleColumns[$column] ? 'hidden' : ''; ?>
                                        <?php switch ($column):
                                            case DailyReportForm::COLUMN_STAFF: ?>
                                                <?php if ($ordKey == 0 && ($cashRowSpan || !$model->isGroupedByStaff())) {
                                                    if ($model->isGroupedByStaff()) {
                                                        $class .= " " . $groupClass . $ind;
                                                    }
                                                    ?>
                                                    <td class="<?= $class ?>" rowspan="<?= $model->isGroupedByStaff() ? $cashRowSpan : $ordRowspan + $rowspan ?>">
                                                        <?= $cashflow->staff->getFullName() ?>
                                                    </td>
                                                <?php } ?>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_DATETIME: ?>
                                                <?php if ($ordKey == 0) { ?>
                                                    <td class="<?= $class ?>" rowspan="<?= $ordRowspan + $rowspan ?>">
                                                        <?= Yii::$app->formatter->asDatetime($cashflow->date) ?>
                                                    </td>
                                                <?php } ?>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_CREATED_AT: ?>
                                                <td class="<?= $class ?>" class="v_middle text-center"
                                                    rowspan="<?= $rowspan ?>">
                                                    <?= Yii::$app->formatter->asDatetime($cashflow->created_at) ?>
                                                </td>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_CUSTOMER: ?>
                                                <?php if ($ordKey == 0 && ($cashRowSpan || !$model->isGroupedByCustomer())) {
                                                    if ($model->isGroupedByCustomer()) {
                                                        $class .= " " . $groupClass . $ind;
                                                    }
                                                    ?>
                                                    <td class="<?= $class ?>" rowspan="<?= $ordRowspan + $rowspan ?>">
                                                        <?= $cashflow->customer->customer->getFullName() ?>
                                                    </td>
                                                <?php } ?>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_COST_ITEM: ?>
                                                <?php if ($cashRowSpan || !$model->isGroupedByCostItem()) {
                                                    if ($model->isGroupedByCostItem()) {
                                                        $class .= " " . $groupClass . $ind;
                                                    }
                                                    ?>
                                                    <td class="<?= $class ?>"
                                                        rowspan="<?= $model->isGroupedByCostItem() ? $cashRowSpan : $rowspan ?>">
                                                        <?= $cashflow->costItem->getFullName() ?>
                                                    </td>
                                                <?php }?>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_SERVICE: ?>
                                                <td class="<?= $class ?>">
                                                    <?= $cashflow->getItemsTitle("<br><br>"); ?>
                                                </td>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_PAID: ?>
                                                <?php $value = $cashflow->getValue() ?>
                                                <td class="<?= $class . ($value < 0 ? ' red' : '') ?>" align="right"
                                                    rowspan="<?= $rowspan ?>">
                                                    <?= Yii::$app->formatter->asDecimal($value) ?>
                                                </td>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_DISCOUNT: ?>
                                                <td class="<?= $class ?>" align="right"
                                                    rowspan="<?= $rowspan ?>">
                                                    <?= Yii::$app->formatter->asDecimal($cashflowDiscount) ?>
                                                </td>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_CASH: ?>
                                                <td class="<?= $class ?>" rowspan='<?= $rowspan ?>'>
                                                    <?= $cashflow->cash->name ?>
                                                </td>
                                                <?php break; ?>

                                            <?php case DailyReportForm::COLUMN_DEPT: ?>
                                                <?php if ($ordKey == 0) { ?>
                                                    <td class="<?= $class ?>" rowspan="<?= $ordRowspan + $rowspan ?>"
                                                        align="right">
                                                        <?= Yii::$app->formatter->asDecimal(abs($debt)) ?>
                                                    </td>
                                                <?php } ?>
                                                <?php break; ?>

                                            <?php endswitch; ?>
                                    <?php endforeach; ?>

                                    <?php
                                    $orderPayments = \yii\helpers\ArrayHelper::map($cashflow->payments,
                                        'payment_id', 'value');
                                    foreach ($paymentsList as $id => $paymentName) {
                                        $value = isset($orderPayments[$id]) ? $orderPayments[$id] : 0;
                                        if ($cashflow->costItem->isIncome()) {
                                            $staffPayments[$id] += $value;
                                        } else {
                                            $staffPayments[$id] -= $value;
                                        }
                                        $formattedValue = Yii::$app->formatter->asDecimal($value);
                                        echo "<td align='right' rowspan='{$rowspan}'>{$formattedValue}</td>";
                                    }
                                    ?>
                                </tr>
                            <?php } ?>
                            <?php $cashRowSpan = 0;
                        } ?>

                        <tr class="daily-report-subfooter">
                            <?php
                            $firstVisible = false;
                            ?>
                            <?php foreach ($model->columns as $column): ?>
                                <?php
                                $class = !$model->visibleColumns[$column] ? 'hidden' : '';
                                ?>
                                <?php switch ($column):
                                    case DailyReportForm::COLUMN_PAID: ?>
                                        <td class="<?= $class ?>" align="right">
                                            <?= Yii::$app->formatter->asDecimal($staffPaid) ?>
                                        </td>
                                        <?php break; ?>

                                    <?php case DailyReportForm::COLUMN_DISCOUNT: ?>
                                        <td class="<?= $class ?>" align="right">
                                            <?= Yii::$app->formatter->asDecimal($staffDiscount) ?>
                                        </td>
                                        <?php break; ?>

                                    <?php case DailyReportForm::COLUMN_DEPT: ?>
                                        <td class="<?= $class ?>" align="right">
                                            <?= Yii::$app->formatter->asDecimal(abs($staffDebt)) ?>
                                        </td>
                                        <?php break; ?>

                                    <?php default: ?>
                                        <td class="<?= $class ?>">
                                            <?php if (!$firstVisible && $model->visibleColumns[$column]) {
                                                $firstVisible = true;
                                                if ($model->total_only) {
                                                    try {
                                                        echo $staff->getFullName();
                                                    } catch (Exception $e) {
                                                        var_dump($e->getMessage());
                                                    }
                                                } else {
                                                    echo "Итого";
                                                }
                                            } ?>
                                        </td>
                                        <?php break; ?>

                                    <?php endswitch; ?>
                            <?php endforeach; ?>

                            <?php foreach ($paymentsList as $id => $iteration):
                                $payments[$id] += $staffPayments[$id]; ?>
                                <td><?= Yii::$app->formatter->asDecimal($staffPayments[$id]) ?></td>
                            <?php endforeach; ?>

                        </tr>
                        <?php
                        $totalPaid += $staffPaid;
                        $totalDiscount += $staffDiscount;
                        $totalDebt += $staffDebt;
                        ?>
                    <?php } ?>
                    <tr class="daily-report-footer">
                        <?php
                        $firstVisible = false;
                        ?>
                        <?php foreach ($model->columns as $column): ?>
                            <?php
                            $class = !$model->visibleColumns[$column] ? 'hidden' : '';
                            ?>
                            <?php switch ($column):
                                case DailyReportForm::COLUMN_PAID: ?>
                                    <td class="<?= $class ?>" align="right">
                                        <?= Yii::$app->formatter->asDecimal($totalPaid) ?>
                                    </td>
                                    <?php break; ?>

                                <?php case DailyReportForm::COLUMN_DISCOUNT: ?>
                                    <td class="<?= $class ?>" align="right">
                                        <?= Yii::$app->formatter->asDecimal($totalDiscount) ?>
                                    </td>
                                    <?php break; ?>

                                <?php case DailyReportForm::COLUMN_DEPT: ?>
                                    <td class="<?= $class ?>" align="right">
                                        <?= Yii::$app->formatter->asDecimal(abs($totalDebt)) ?>
                                    </td>
                                    <?php break; ?>

                                <?php default: ?>
                                    <td class="<?= $class ?>">
                                        <?php if (!$firstVisible && $model->visibleColumns[$column]) {
                                            $firstVisible = true;
                                            echo "Итого за период";
                                        } ?>
                                    </td>
                                    <?php break; ?>

                                <?php endswitch; ?>
                        <?php endforeach; ?>
                        <?php foreach ($payments as $payment): ?>
                            <td align="right" class="v_middle text-center">
                                <?= Yii::$app->formatter->asDecimal($payment) ?>
                            </td>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php
$this->registerJsFile('https://unpkg.com/sticky-table-headers', [
    'position' => \yii\web\View::POS_END,
    'depends'  => \yii\web\JqueryAsset::class
]);

$rowspans = json_encode($rowspans);
$setRowspan = intval($model->isGroupedByStaff() || $model->isGroupedByCustomer() || $model->isGroupedByCostItem());

$js = <<<JS
$('.data_table > table').stickyTableHeaders();

$('.js-toggle-all-columns').change(function(event) {
    if (this.checked) {
       $('.js-visible-column').not(':checked').prop('checked', true);
    }else{
        $('.js-visible-column:checked').prop('checked', false);
    }
});

$('.js-visible-column').change(function(event) {
    if (!this.checked) {
        $('.js-toggle-all-columns').prop('checked', false);
    } else {
        if ($('.js-visible-column').not(':checked').length == 0) {
            $('.js-toggle-all-columns').prop('checked', true);
        }
    }
});

var rowspans = $rowspans;
if ($setRowspan) {
    for (ind in rowspans) {
        if (rowspans.hasOwnProperty(ind)) {
            $('.js-groupped-column-' + ind).attr('rowspan', rowspans[ind]);
        }
    }
}
JS;

$this->registerJs($js);
?>