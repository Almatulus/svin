<?php
use yii\helpers\Html;

?>
<div class="info-box">
    <div class="box-title">Прошедшие записи: <?= count($orders_passed) ?></div>
    <?php if (count($orders_passed) == 0): ?>
        <h3>Нет прошедших записей</h3>
    <?php endif; ?>
    <?php foreach($orders_passed as $key => $order): ?>
        <div class="box-rows lbl">
            <div class="light row">
                <div class="col-xs-7">
                    <div class="bigger overflow_with_ellipsis wrap-white-space">
                        <?= Html::a($order->servicesTitle,
                            "javascript:;",
                            [
                                'data-tab_name' => "events_history",
                                'class' => 'pjax_link lbl',
                                'title' => $order->servicesTitle
                            ]); ?>
                    </div>
                    <div class="overflow_with_ellipsis">
                        <?php
                            $datetime = new DateTime($order->datetime);
                            echo $datetime->format("d/m/Y H:i");
                        ?>
                    </div>
                    <div class="overflow_with_ellipsis">
                        <?= $order->note ?>
                    </div>
                </div>
                <div class="col-xs-3 text-right">
                    <div class="border-color">
                        <?= Html::a($order->staff->getFullname(),
                            ['/staff/view', 'id' => $order->staff_id],
                            [
                                'class' => $order->staff->color,
                                'data-placement' => "top",
                                'data-toggle'=>'tooltip',
                            ])?>
                    </div>
                </div>
                <div class="col-xs-2 text-right"><?= number_format($order->price, 0, '', ' ') ?> <?= Yii::t('app', 'Currency')?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>