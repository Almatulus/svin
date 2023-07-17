<?php

use core\models\division\Division;
use core\models\finance\CompanyCash;
use core\models\finance\CompanyCostItem;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use core\models\Payment;

/* @var $this yii\web\View */
/* @var $model core\forms\finance\ReportForm */
/* @var $expenseCostItems CompanyCostItem[] */
/* @var $incomeCostItems CompanyCostItem[] */
/* @var $period DatePeriod */
/* @var $cashFlows array */

$this->title = Yii::t('app', 'Financial Report');

$this->params['breadcrumbs'][] = [
    'template' => '<li><div class="icon sprite-breadcrumbs_statistics"></div>{link} </li>',
    'label' => $this->title
];
$this->params['bodyID']        = 'finance';
$costItemClass = $model->showOnlyCategories ? "only-category" : "";
?>
<div class="finance-report">
    <div class="finance-report-filters">
        <?php $form = ActiveForm::begin([
            'action' => ['report/period'],
            'fieldConfig' => [ 'template' => "{input}\n{hint}\n{error}" ],
            'method' => 'get',
        ]); ?>
        <div class="row">
            <div class="col-md-12">
                <h3><?= Yii::t('app', 'Filters') ?></h3>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model, 'from', [
                    'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app', 'From date') . '</span>{input}</div>',
                ])->widget(DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd'
                    ]
                ]); ?>
            </div>
            <div class="col-md-2">
                <?=
                $form->field($model, 'to', [
                    'template' => '<div class="input-group"><span class="input-group-addon">' . Yii::t('app', 'To date') . '</span>{input}</div>',
                ])->widget(DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd'
                    ]
                ]); ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'cash')->widget(Select2::class, [
                    'data' => CompanyCash::map(),
                    'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Cashes')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'language' => 'ru'
                    ],
                    'size' => 'sm',
                    'showToggleAll' => false,
                    'theme' => Select2::THEME_CLASSIC,
                ])->label(false); ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'cost_item')->widget(Select2::class, [
                    'data' => CompanyCostItem::map(),
                    'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Cost Items')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'language' => 'ru'
                    ],
                    'size' => 'sm',
                    'showToggleAll' => false,
                    'theme' => Select2::THEME_CLASSIC,
                ])->label(false); ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'division')->widget(Select2::class, [
                    'data' => Division::getOwnDivisionsNameList(),
                    'options' => ['multiple' => false, 'placeholder' => Yii::t('app', 'All Divisions')],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'language' => 'ru'
                    ],
                    'size' => 'sm',
                    'showToggleAll' => false,
                    'theme' => Select2::THEME_CLASSIC,
                ])->label(false); ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'payments')->widget(\kartik\select2\Select2::class, [
                    'data' => Payment::getPaymentsList(),
                    'options' => ['multiple' => true],
                    'size' => 'sm',
                ]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <?= $form->field($model, 'showDetailing')->checkbox() ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'showOnlyCategories')->checkbox(['class'=>'show-only-categories']) ?>
            </div>
            <div class="col-md-8">
                <div class="pull-left">
                    <?php
                    echo sprintf('Выбран период длительностью %d дней', $model->dateDifference);
                    ?>
                </div>
                <div class="form-group pull-right">
                    <?= Html::a(Yii::t('app', 'Export'), 'period-export?' . Yii::$app->request->queryString, ['class' => 'btn btn-default js-export-report']) ?>
                    <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
        <?php $form->end() ?>
    </div>
    <?php $remainders = []; ?>
    <div class="data_table">
        <table class="finance-report-table">
            <thead>
            <tr>
                <th class="nowrap" rowspan="2"><b><?= Yii::t('app', 'Cost Item'); ?></b></th>
                <?php
                foreach ($period as $dt) {
                    $date = Yii::$app->formatter->asDate($dt->format('Y-m-d'), 'php:d F');
                    echo "<td colspan='2' class='nowrap'><b>{$date}</b></td>";
                }
                ?>
                <td class="nowrap" colspan="2"><b>Итого</b></td>
            </tr>
            <tr>
                <?php
                foreach ($period as $dt) {
                    $date = Yii::$app->formatter->asDate($dt->format('Y-m-d'), 'php:d F');
                    echo "<td class='nowrap'>нал</td><td class='nowrap'>б/н</td>";
                }
                ?>
                <td class='nowrap'>нал</td><td class='nowrap'>б/н</td>
            </tr>
            </thead>
            <tbody>
            <?php
            $categoryId = 0;
            $categoryName = '';
            $categoryData = [];
            foreach ($incomeCostItems as $costItem) {
                if ($categoryId !== $costItem->category_id && $categoryId !== 0) {
                    showCategoryRow($categoryName, $categoryData, $period);
                    // Clear category data
                    $categoryData = [];
                }
                $categoryId = $costItem->category_id;
                $categoryName = $costItem->category_id ? $costItem->category->name : 'Общий';

                echo "<tr class='{$costItemClass} cost-item-row'>";
                echo "<th class='normal-font'>{$costItem->getFullName()}</th>";
                $totalCash = 0;
                $totalNotCash = 0;
                foreach ($period as $key => $dt) {
                    $date  = $dt->format("Y-m-d");
                    $cash = $cashFlows[$costItem->id][$date]['cash'] ?? 0;
                    $notCash = $cashFlows[$costItem->id][$date]['not_cash'] ?? 0;
                    $totalCash += $cash;
                    $totalNotCash += $notCash;
                    echo Html::tag('td', Yii::$app->formatter->asDecimal($cash));
                    echo Html::tag('td', Yii::$app->formatter->asDecimal($notCash));
                    // Category data
                    if (!isset($categoryData[$date])) {
                        $categoryData[$date] = ['cash' => 0, 'not_cash' => 0];
                    }
                    $categoryData[$date]['cash'] += $cash;
                    $categoryData[$date]['not_cash'] += $notCash;
                }
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalCash)));
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalNotCash)));
                echo "</tr>";
            }
            showCategoryRow($categoryName, $categoryData, $period);
            ?>
            <?php if (!empty($incomeCostItems)) { ?>
                <tr>
                    <th><b><?= Yii::t('app', 'CostItem Income'); ?></b></td>
                    <?php
                    $totalCash = 0;
                    $totalNotCash = 0;
                    foreach ($period as $dt) {
                        $date  = $dt->format("Y-m-d");
                        $cash = $cashFlows["total"][CompanyCostItem::TYPE_INCOME][$date]['cash'] ?? 0;
                        $notCash = $cashFlows["total"][CompanyCostItem::TYPE_INCOME][$date]['not_cash'] ?? 0;
                        $totalCash += $cash;
                        $totalNotCash += $notCash;
                        echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($cash)));
                        echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($notCash)));
                    }
                    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalCash)));
                    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalNotCash)));
                    ?>
                </tr>
            <?php } ?>
            <?php
            $categoryId = 0;
            $categoryName = '';
            $categoryData = [];
            foreach ($expenseCostItems as $costItem) {
                if ($categoryId !== $costItem->category_id && $categoryId !== 0) {
                    showCategoryRow($categoryName, $categoryData, $period);
                    // Clear category data
                    $categoryData = [];
                }
                $categoryId = $costItem->category_id;
                $categoryName = $costItem->category_id ? $costItem->category->name : 'Общий';

                echo "<tr class='{$costItemClass} cost-item-row'>";
                echo "<th class='normal-font'>{$costItem->getFullName()}</th>";
                $totalCash = 0;
                $totalNotCash = 0;
                foreach ($period as $key => $dt) {
                    $date  = $dt->format("Y-m-d");
                    $cash = $cashFlows[$costItem->id][$date]['cash'] ?? 0;
                    $notCash = $cashFlows[$costItem->id][$date]['not_cash'] ?? 0;
                    $totalCash += $cash;
                    $totalNotCash += $notCash;
                    echo Html::tag('td', Yii::$app->formatter->asDecimal($cash));
                    echo Html::tag('td', Yii::$app->formatter->asDecimal($notCash));
                    // Category data
                    if (!isset($categoryData[$date])) {
                        $categoryData[$date] = ['cash' => 0, 'not_cash' => 0];
                    }
                    $categoryData[$date]['cash'] += $cash;
                    $categoryData[$date]['not_cash'] += $notCash;
                }
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalCash)));
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalNotCash)));
                echo "</tr>";
            }
            showCategoryRow($categoryName, $categoryData, $period);
            ?>
            <?php if (!empty($expenseCostItems)) { ?>
                <tr>
                    <th><b><?= Yii::t('app', 'CostItem Expense'); ?></b></th>
                    <?php
                    $totalCash = 0;
                    $totalNotCash = 0;
                    foreach ($period as $dt) {
                        $date  = $dt->format("Y-m-d");
                        $cash = $cashFlows["total"][CompanyCostItem::TYPE_EXPENSE][$date]['cash'] ?? 0;
                        $notCash = $cashFlows["total"][CompanyCostItem::TYPE_EXPENSE][$date]['not_cash'] ?? 0;
                        $totalCash += $cash;
                        $totalNotCash += $notCash;
                        echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($cash)));
                        echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($notCash)));
                    }
                    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalCash)));
                    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalNotCash)));
                    ?>
                </tr>
            <?php } ?>
            <tr>
                <th rowspan="2"><b>Итого</b>
                </td>
                <?php
                $totalCash = 0;
                $totalNotCash = 0;
                foreach ($period as $dt) {
                    $date  = $dt->format("Y-m-d");
                    $incomeCash = $cashFlows["total"][CompanyCostItem::TYPE_INCOME][$date]['cash'] ?? 0;
                    $incomeNotCash = $cashFlows["total"][CompanyCostItem::TYPE_INCOME][$date]['not_cash'] ?? 0;
                    $expenseCash = $cashFlows["total"][CompanyCostItem::TYPE_EXPENSE][$date]['cash'] ?? 0;
                    $expenseNotCash = $cashFlows["total"][CompanyCostItem::TYPE_EXPENSE][$date]['not_cash'] ?? 0;
                    $totalCash += $incomeCash - $expenseCash;
                    $totalNotCash += $incomeNotCash - $expenseNotCash;
                    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($incomeCash - $expenseCash)),
                        ['align' => 'center']);
                    echo Html::tag('td',
                        Html::tag('b', Yii::$app->formatter->asDecimal($incomeNotCash - $expenseNotCash)),
                        ['align' => 'center']);
                }
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalCash)),
                    ['align' => 'center']);
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalNotCash)),
                    ['align' => 'center']);
                ?>
            </tr>
            <tr>
                <?php
                $total = 0;
                foreach ($period as $dt) {
                    $date = $dt->format("Y-m-d");
                    $incomeCash = $cashFlows["total"][CompanyCostItem::TYPE_INCOME][$date]['cash'] ?? 0;
                    $incomeNotCash = $cashFlows["total"][CompanyCostItem::TYPE_INCOME][$date]['not_cash'] ?? 0;
                    $expenseCash = $cashFlows["total"][CompanyCostItem::TYPE_EXPENSE][$date]['cash'] ?? 0;
                    $expenseNotCash = $cashFlows["total"][CompanyCostItem::TYPE_EXPENSE][$date]['not_cash'] ?? 0;
                    $value = $incomeCash + $incomeNotCash - $expenseCash - $expenseNotCash;
                    $total += $value;
                    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($value)),
                        ['colspan' => 2, 'align' => 'center']);
                }
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($total)),
                    ['colspan' => 2, 'align' => 'center']);
                ?>
            </tr>
            <tr>
                <th><b>Остаток на начало дня</b></th>
                <?php
                $previousBalance = $model->getStartBalance();
                foreach ($period as $dt) {
                    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($previousBalance)), ['colspan' => 2, 'align' => 'center']);
                    $date  = $dt->format("Y-m-d");
                    $incomeCash = $cashFlows["total"][CompanyCostItem::TYPE_INCOME][$date]['cash'] ?? 0;
                    $incomeNotCash = $cashFlows["total"][CompanyCostItem::TYPE_INCOME][$date]['not_cash'] ?? 0;
                    $expenseCash = $cashFlows["total"][CompanyCostItem::TYPE_EXPENSE][$date]['cash'] ?? 0;
                    $expenseNotCash = $cashFlows["total"][CompanyCostItem::TYPE_EXPENSE][$date]['not_cash'] ?? 0;
                    $previousBalance = $previousBalance + $incomeCash + $incomeNotCash - $expenseCash - $expenseNotCash;
                    $remainders[$date] = $previousBalance;
                }
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($previousBalance)), ['colspan' => 2, 'align' => 'center']);
                ?>
            </tr>
            <tr>
                <th><b><?= Yii::t('app', 'CostItem Remainder'); ?></b></td>
                <?php
                $total = 0;
                foreach ($period as $dt) {
                    $date  = $dt->format("Y-m-d");
                    $localTotal = $remainders[$date] ?? 0;
                    $total += $localTotal;
                    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($localTotal)),
                        ['colspan' => 2, 'align' => 'center']);
                }
                echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($total)), ['colspan' => 2, 'align' => 'center']);
                ?>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
$this->registerJs("$('.finance-report-table').tableHeadFixer({'left' : 1});");
?>

<?php
$script = "
$('input#showonlycategories').on('change', function() {
    console.log($('.cost-item-row'));
    if ($(this).is(':checked')) {
        $('.cost-item-row').hide();
    } else {
        $('.cost-item-row').show();
    }
});
";
    $this->registerJs($script);
?>

<?php
function showCategoryRow($categoryName, $categoryData, $period) {
    echo "<tr class='category-row'>";
    echo "<th class='normal-font'><i>Категория: {$categoryName}</i></th>";
    $totalCategoryCash = 0;
    $totalCategoryNotCash = 0;
    foreach ($period as $key => $dt) {
        $date  = $dt->format("Y-m-d");
        $cash = $categoryData[$date]['cash'] ?? 0;
        $notCash = $categoryData[$date]['not_cash'] ?? 0;
        $totalCategoryCash += $cash;
        $totalCategoryNotCash += $notCash;
        echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($cash)));
        echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($notCash)));
    }
    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalCategoryCash)));
    echo Html::tag('td', Html::tag('b', Yii::$app->formatter->asDecimal($totalCategoryNotCash)));
    echo "</tr>";
}
?>
<style>
    .data_table {
        width: 100%; 
        height: calc(100vh - 300px);
        overflow: scroll; 
        max-height: none; 
        min-height: 0px; 
        max-width: none; 
        min-width: 0px;
    }
    .data_table .category-row th,
    .data_table .category-row td {
        background-color: #f5f8fa
    }

    .only-category {
        display: none;
    }
</style>
