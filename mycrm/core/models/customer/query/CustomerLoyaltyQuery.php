<?php

namespace core\models\customer\query;

use yii\db\ActiveQuery;

class CustomerLoyaltyQuery extends ActiveQuery
{
    /**
     * Filter by company_id
     * @param integer $company_id
     * @return CustomerLoyaltyQuery
     */
    public function company($company_id = null)
    {
        if ($company_id == null) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        return $this->andWhere(['crm_customer_loyalties.company_id' => $company_id]);
    }

    /**
     * @param int $event
     * @return $this
     */
    public function byEvent(int $event)
    {
        return $this->andWhere(['{{%customer_loyalties}}.event' => $event]);
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function byMode(int $mode)
    {
        return $this->andWhere(['{{%customer_loyalties}}.mode' => $mode]);
    }
}