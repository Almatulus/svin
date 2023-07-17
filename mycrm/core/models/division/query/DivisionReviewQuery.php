<?php

namespace core\models\division\query;

use common\components\traits\DivisionTrait;
use yii\db\ActiveQuery;

class DivisionReviewQuery extends ActiveQuery
{
    use DivisionTrait;

    /**
     * Filter by division id
     * @param integer $division_id
     * @return DivisionReviewQuery
     */
    public function division($division_id)
    {
        return $this->andWhere(['crm_division_reviews.division_id' => $division_id]);
    }

    /**
     * @return string
     */
    public function getDivisionAttribute()
    {
        return "{{%division_reviews}}.division_id";
    }
}