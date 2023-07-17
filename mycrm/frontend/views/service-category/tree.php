<?php

use common\components\TreeBuilder;
use core\models\ServiceCategory;

?>

<ul class='simple-tree'>
    <li class='node root'>
        <div class='item'>
            <div class='icon_box'>
                <i class='icon sprite-filter_purchased_services'></i>
            </div>
            <?= Yii::t('app', 'All services'); ?>
        </div>
        <?= TreeBuilder::widget([
            'models'              => ServiceCategory::getDynamicCategories(),
            'childrenAttribute'   => "serviceCategories",
            'editButtonOptions'   => ['class' => 'btn-edit-category'],
            'deleteButtonOptions' => ['class' => 'btn-delete-category'],
            'editButtonVisible'   => Yii::$app->user->can('serviceCategoryUpdate'),
            'deleteButtonVisible' => Yii::$app->user->can('serviceCategoryDelete'),
        ]); ?>
    </li>
</ul>