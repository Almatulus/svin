<?php
use yii\helpers\Html;

?>

<style>
    .empty {
        padding-left: 30px;
        /*width           : 100vw;*/
        height: vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
</style>

<div class="empty">
    <h1>В вашем заведении нет сотрудников</h1>
    <h2><?= Html::a("Добавить сотрудников", ['/staff/index']) ?></h2>
</div>
