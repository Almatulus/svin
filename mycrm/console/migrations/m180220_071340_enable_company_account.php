<?php

use core\models\company\Company;
use yii\db\Migration;

/**
 * Class m180220_071340_disable_company_account
 */
class m180220_071340_enable_company_account extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update(Company::tableName(), ['status' => Company::STATUS_ENABLED]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }
}
