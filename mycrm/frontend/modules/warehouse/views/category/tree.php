<?php
use common\components\TreeBuilder;
use core\models\warehouse\Category;

?>

<ul class='simple-tree'>
    <li class='node root'>
        <div class='item'>
            <div class='icon_box'>
                <i class='icon sprite-breadcrumbs_stock'></i>
            </div>
            <?= Yii::t('app', 'All Products'); ?>
        </div>
        <?= TreeBuilder::widget([
            'models' => Category::getCompanyCategories(),
            'childrenAttribute' => "categories",
            'editButtonOptions' => ['class' => 'btn-edit-category'],
            'deleteButtonOptions' => ['class' => 'btn-delete-category'],
        ]); ?>
    </li>
</ul>