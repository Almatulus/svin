<div class="column_row secondary_menu">
    <div class="pull_left">
        <ul class="simple-list">
            <li class="<?= strpos(Yii::$app->request->url, 'warehouse/product') !== false ? 'active' : '' ?>">
                <a href="/warehouse/product/index">
                    <i class="icon sprite-stock_products"></i> <?= Yii::t('app', 'Products') ?>
                </a>
            </li>
            <li class="<?= strpos(Yii::$app->request->url, 'warehouse/sale') !== false ? 'active' : '' ?>">
                <a href="/warehouse/sale/index">
                    <i class="icon sprite-stock_sale_on"></i> <?= Yii::t('app', 'Sales') ?>
                </a>
            </li>
            <li class="<?= strpos(Yii::$app->request->url, 'warehouse/usage') !== false ? 'active' : '' ?>">
                <a href="/warehouse/usage/index">
                    <i class="icon sprite-stock_consumption"></i> <?= Yii::t('app', 'Usage') ?>
                </a>
            </li>
            <li class="<?= (strpos(Yii::$app->request->url, 'warehouse/delivery') !== false 
            || strpos(Yii::$app->request->url, 'warehouse/manufacturer') !== false) ? 'active' : '' ?>">
                <a href="/warehouse/delivery/index">
                    <i class="icon sprite-stock_delivery"></i> <?= Yii::t('app', 'Deliveries') ?>
                </a>
            </li>
        </ul>
    </div>
    <div class="pull_right">
        <ul class="simple-list">
            <li class="<?= strpos(Yii::$app->request->url, 'warehouse/stocktake') !== false ? 'active' : '' ?>">
                <a href="/warehouse/stocktake/index">
                    <i class="icon sprite-stock_stocktaking"></i> <?= Yii::t('app', 'Stocktake') ?>
                </a>
            </li>
        </ul>
    </div>
    <br class="c">
</div>