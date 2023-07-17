<?php

use core\models\company\Company;
use yii\db\Migration;

/**
 * Class m180614_031652_move_dental_companies_to_health_category
 */
class m180614_031652_move_dental_companies_to_health_category extends Migration
{
    const CATEGORY_HEALTH = 14;
    const CATEGORY_DENTAL = 124;
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            Company::tableName(),
            ['category_id' => self::CATEGORY_HEALTH],
            ['category_id' => self::CATEGORY_DENTAL]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
