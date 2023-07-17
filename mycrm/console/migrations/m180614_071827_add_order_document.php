<?php

use core\models\order\OrderDocumentTemplate;
use yii\db\Migration;

/**
 * Class m180614_071827_add_order_document
 */
class m180614_071827_add_order_document extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert(OrderDocumentTemplate::tableName(), [
            'name'        => 'Договор на Эндодонтическое лечение',
            'filename'    => 'Договор_на_Эндодонтическое_лечение.docx',
            'category_id' => 124,
            'company_id'  => null,
            'path'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete(OrderDocumentTemplate::tableName(), [
            'name'        => 'Договор на Эндодонтическое лечение',
            'filename'    => 'Договор_на_Эндодонтическое_лечение.docx',
            'category_id' => 124,
            'company_id'  => null,
            'path'        => null,
        ]);
    }
}
