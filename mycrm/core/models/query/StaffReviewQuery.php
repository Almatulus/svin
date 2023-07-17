<?php

namespace core\models\query;
use yii\db\ActiveQuery;

class StaffReviewQuery extends ActiveQuery
{
    /**
     * Filter by staff
     * @param integer $staff_id
     * @return StaffReviewQuery
     */
    public function staff($staff_id)
    {
        return $this->andWhere(['crm_staff_reviews.staff_id' => $staff_id]);
    }

    /**
     * Filter by customer_id
     * @param integer $customer_id
     * @return StaffReviewQuery
     */
    public function customer($customer_id)
    {
        return $this->andWhere(['crm_staff_reviews.customer_id' => $customer_id]);
    }

    /**
     * Filter by permitted divisions
     * @return StaffReviewQuery
     */
    public function permitted()
    {
        $divisions = \Yii::$app->user->identity->permittedDivisions;
        if ($divisions) {
            return $this->joinWith('staff.divisions', false)->andFilterWhere(['{{%staff_division_map}}.division_id' => $divisions]);
        }
        return $this;
    }
}