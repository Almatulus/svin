<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\search\CustomerSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="customer-search-contact">
    <?php
        echo Html::activeTextInput($model, 'sContact', ['class' => 'right_space', 'placeholder' => Yii::t('app', 'Search Contact')]);
        echo Html::submitButton(Yii::t('app', 'Find'), ['class' => 'btn btn-primary']);
    ?>
</div>