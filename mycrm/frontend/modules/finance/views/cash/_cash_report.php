<?php

use yii\helpers\Html;

/* @var $model core\models\finance\CompanyCash */
?>

<div class="column_row buttons-row">
    <div class="right-buttons">
        <div class="dropdown inline_block">
            <button class="btn btn_dropdown" data-toggle="dropdown"
                    aria-expanded="false">
                Действия <b class="caret"></b>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <?= Html::a('<i class="fa fa-pencil"></i> '
                                . Yii::t('app', 'Edit'),
                        ['edit', 'id' => $model->id], [
                            'id' => 'js-edit-cash'
                        ]) ?>
                </li>
                <?php if ($model->is_deletable) { ?>
                    <li>
                        <?= Html::a('<i class="fa fa-trash"></i> '
                            . Yii::t('app', 'Delete'),
                            ['delete', 'id' => $model->id], [
                                'id' => 'js-delete-cash'
                            ]) ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <?= Html::a(Yii::t('app', 'Create Cashflow Income'),
            ['cashflow/create-income', 'id' => $model->id],
            ['class' => 'btn']) ?>
        <?= Html::a(Yii::t('app', 'Create Cashflow Expense'),
            ['cashflow/create-expense', 'id' => $model->id],
            ['class' => 'btn']) ?>
        <?php
        if (sizeof($cashes) > 0) {
            echo Html::a(Yii::t('app', 'Transfer'),
                ['transfer', 'id' => $model->id], ['id' => 'js-transfer-cash', 'class' => 'btn']);
        }
        ?>
    </div>
</div>

<div class="column_row">
    <div class="row">
        <div class="col-sm-3">
            <div class="cash-register-box primary-box">
                <div class="box-title">Текущий баланс</div>
                <div class="box-value">
                    <?= Html::a(Yii::$app->formatter->asDecimal($model->balance),
                        [
                            'cashflow/index',
                            'sFrom' => '',
                            'sTo'   => '',
                            'sCash' => $model->id
                        ]) ?>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="cash-register-box income-box">
                <div class="box-title">Поступления</div>
                <div class="box-value">
                    <?= Html::a(Yii::$app->formatter->asDecimal($model->income),
                        [
                            'cashflow/index',
                            'sFrom' => '',
                            'sTo'   => '',
                            'sCost' => -1,
                            'sCash' => $model->id
                        ]) ?>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="cash-register-box expenses-box">
                <div class="box-title">
                    <?= Yii::t('app', 'CostItem Expense') ?>
                </div>
                <div class="box-value">
                    <?= Html::a(Yii::$app->formatter->asDecimal($model->expense),
                        [
                            'cashflow/index',
                            'sFrom' => '',
                            'sTo'   => '',
                            'sCost' => -2,
                            'sCash' => $model->id
                        ]) ?>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="cash-register-box">
                <div class="box-title">Начальный баланс</div>
                <div class="box-value">
                    <?= Yii::$app->formatter->asDecimal($model->init_money) ?>
                </div>
                <div class="box-footer">
                    <!--                    на:<strong>22.04.2016 13:34</strong>-->
                </div>
            </div>
        </div>

        <?php
        $numberOfMonths = 3;
        $months = \core\helpers\DateHelper::getPreviousMonths($numberOfMonths);
        for ($i = $numberOfMonths - 1; $i >= 0; $i--) { ?>
            <div class="col-sm-3">
                <div class="cash-register-box primary-box">
                    <div class="box-title"><?= "Сальдо за " . Yii::t('app',
                            $months[$i]->format("F")) . " " . $months[$i]->format("Y г.") ?></div>
                    <div class="box-value">
                        <?= Html::a(Yii::$app->formatter->asDecimal($model->getIncome($months[$i]) - $model->getExpense($months[$i])),
                            [
                                'cashflow/index',
                                'sFrom' => $months[$i]->format("Y-m-d"),
                                'sTo'   => $months[$i]->modify("+1 month -1 day")->format("Y-m-d"),
                                'sCost' => '',
                                'sCash' => $model->id
                            ]) ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
