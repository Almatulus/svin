<?php

namespace core\models\query;

use yii\db\ActiveQuery;

class CommentTemplateCategoryQuery extends ActiveQuery
{
    /**
     * @param int $service_category_id
     * @return CommentTemplateCategoryQuery
     */
    public function serviceCategory(int $service_category_id)
    {
        return $this->andWhere(['{{%med_card_comment_categories}}.service_category_id' => $service_category_id]);
    }
}