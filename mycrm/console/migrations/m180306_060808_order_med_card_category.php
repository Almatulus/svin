<?php

use core\models\medCard\MedCardCommentCategory;
use yii\db\Migration;

/**
 * Class m180306_060808_order_med_card_category
 */
class m180306_060808_order_med_card_category extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            MedCardCommentCategory::tableName(),
            'order',
            $this->integer()->notNull()->defaultValue(1)
        );

        /* @var MedCardCommentCategory[] $categories */
        $categories = MedCardCommentCategory::find()->orderBy('id')->all();
        $i = 1;
        foreach ($categories as $category) {
            $category->order = $i++;
            $category->update();

            $i == 9 ? $i++ : null;
        }

        $category                      = new MedCardCommentCategory();
        $category->name                = 'План лечения';
        $category->order               = 9;
        $category->service_category_id = 124;
        $category->insert(false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(MedCardCommentCategory::tableName(), 'order');
    }
}
