<?php

use core\models\company\Company;
use yii\db\Migration;

/**
 * Class m180703_164438_show_fullcalendar_view
 */
class m180703_164438_show_fullcalendar_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Company::tableName(), 'show_fullcalendar_view', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Company::tableName(), 'show_fullcalendar_view');
    }
}
