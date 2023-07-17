<?php

/** @var \core\models\customer\CompanyCustomer $model */

if (empty($model->orders)) {
    return "<div class='col-sm-12'>Ничего не найдено.</div>";
} else { ?>

    <div class="col-sm-3"><b>Дата</b></div>
    <div class="col-sm-3"><b>Цена</b></div>
    <div class="col-sm-3"><b>Оплачено</b></div>
    <div class="col-sm-3"><b>Депозит\Долг</b></div>

    <?php foreach ($model->orders as $ind => $order) {
        echo "<div class='col-sm-3'>" . \yii\helpers\Html::a(Yii::$app->formatter->asDatetime($order->datetime), [
                '/order/order/index',
                'number'    => $order->number,
                'from_date' => '',
                'to_date'   => '',
            ]) . "</div>";
        echo "<div class='col-sm-3'>" . Yii::$app->formatter->asDecimal($order->price) . "</div>";
        echo "<div class='col-sm-3'>" . Yii::$app->formatter->asDecimal($order->getOrderPayments()->sum('amount')) . "</div>";
        echo "<div class='col-sm-3'>" . Yii::$app->formatter->asDecimal($order->payment_difference) . "</div>";
        ?>

        <?php
    }
} ?>
