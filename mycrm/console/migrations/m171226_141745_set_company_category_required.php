<?php

use core\models\company\Company;
use yii\db\Migration;

/**
 * Class m171226_141745_set_company_category_required
 */
class m171226_141745_set_company_category_required extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        Company::updateAll(['category_id' => 2], ['category_id' => null]);

        $sql = <<<SQL
ALTER TABLE {{%companies}} ALTER COLUMN category_id SET NOT NULL;
SQL;
        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }
}
