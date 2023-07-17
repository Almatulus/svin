<?php

use core\models\division\DivisionServiceProduct;
use yii\db\Migration;

/**
 * Class m180205_061925_remove_deleted_products_from_services
 */
class m180205_061925_remove_deleted_products_from_services extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $serviceProductIds = DivisionServiceProduct::find()->joinWith('product', false)
            ->select('{{%division_service_products}}.id')
            ->where(['{{%warehouse_product}}.status' => \core\models\warehouse\Product::STATUS_DISABLED])
            ->column();

        DivisionServiceProduct::deleteAll(['id' => $serviceProductIds]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180205_061925_remove_deleted_products_from_services cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180205_061925_remove_deleted_products_from_services cannot be reverted.\n";

        return false;
    }
    */
}
