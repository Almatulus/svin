<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model core\models\customer\CustomerLoyalty */
?>
<div class="customer-loyalty-view">
 
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'event',
            'amount',
            'discount',
            'rank',
            'category_id',
            'mode',
        ],
    ]) ?>

</div>
