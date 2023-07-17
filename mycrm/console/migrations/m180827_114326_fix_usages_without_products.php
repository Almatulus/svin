<?php

use core\models\warehouse\Usage;
use yii\db\Migration;

/**
 * Class m180827_114326_fix_usages_without_products
 */
class m180827_114326_fix_usages_without_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /** @var Usage[] $usages */
        $usages = Usage::find()->distinct()->innerJoinWith('order')
            ->joinWith('usageProducts')
            ->andWhere('{{%warehouse_usage_product}}.id IS NULL')
            ->andWhere(['>=', 'created_at', '2018-08-23'])
            ->enabled()
            ->all();

        foreach ($usages as $usage) {
            if (empty($usage->usageProducts)) {
                $this->update(Usage::tableName(), ['status' => Usage::STATUS_INACTIVE], ['id' => $usage->id]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180827_114326_fix_usages_without_products cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180827_114326_fix_usages_without_products cannot be reverted.\n";

        return false;
    }
    */
}
