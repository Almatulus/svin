<?php

use core\models\ServiceCategory;
use yii\db\Migration;

/**
 * Class m180705_094755_hide_root_categories_except_health_and_beauty
 */
class m180705_094755_hide_root_categories_except_health_and_beauty extends Migration
{
    const CATEGORY_HEALTH = 14;
    const CATEGORY_DENTAL = 124;
    const CATEGORY_MRT = 1432;
    const CATEGORY_RECEPTIONS = 1077;
    const CATEGORY_AUTO = 3;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            ServiceCategory::tableName(),
            ["status" => ServiceCategory::STATUS_DISABLED],
            ["id" => self::CATEGORY_AUTO]
        );
        $this->update(
            ServiceCategory::tableName(),
            ["parent_category_id" => self::CATEGORY_HEALTH],
            ["id" => [self::CATEGORY_DENTAL, self::CATEGORY_MRT, self::CATEGORY_RECEPTIONS]]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update(
            ServiceCategory::tableName(),
            ["parent_category_id" => null],
            ["id" => [self::CATEGORY_DENTAL, self::CATEGORY_MRT, self::CATEGORY_RECEPTIONS]]
        );
        $this->update(
            ServiceCategory::tableName(),
            ["status" => ServiceCategory::STATUS_ENABLED],
            ["id" => self::CATEGORY_AUTO]
        );
    }
}
