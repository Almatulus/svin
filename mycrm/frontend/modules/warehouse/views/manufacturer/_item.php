<?php
use yii\helpers\Html;

/* @var $model core\models\warehouse\Manufacturer */
?>
<div class="details_row condensed">
    <h2 class="col_1">
        <?= Html::a($model->name, ['update', 'id' => $model->id], ['class' => 'blue_text']) ?>
    </h2>
    <div class="col_2">
        <span class="lbl left_lbl"><?= Yii::t('app', 'Number of products') ?></span>
        <?= $model->getProducts()->count() ?>
    </div>
</div>