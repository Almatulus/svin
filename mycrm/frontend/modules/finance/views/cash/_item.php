<?php

/* @var $this yii\web\View */

use yii\helpers\Url;

/* @var $model core\models\finance\CompanyCash */
?>

<div class="col-sm-3">
    <div class="cash-register-box primary-box">
        <a href="<?= Url::toRoute(['cash/view', 'id' => $model->id]) ?>">
            <div class="box-title"><b><?= $model->name ?></b></div>
        </a>

        <div class="box-title">Текущий баланс</div>
        <b><?= Yii::$app->formatter->asDecimal($model->balance); ?></b>
    </div>
</div>
