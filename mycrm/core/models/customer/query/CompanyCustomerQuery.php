<?php

namespace core\models\customer\query;

use yii\db\ActiveQuery;

class CompanyCustomerQuery extends ActiveQuery
{
    /**
     * Filter by company_id
     *
     * @param integer $company_id
     *
     * @return CompanyCustomerQuery
     */
    public function company($company_id = null)
    {
        if ($company_id == null) {
            $company_id = \Yii::$app->user->identity->company_id;
        }

        return $this->andWhere(['{{%company_customers}}.company_id' => $company_id]);
    }

    /**
     * @param string $phone
     * @return CompanyCustomerQuery
     */
    public function phone($phone)
    {
        return $this->joinWith(['customer c'])
            ->andWhere('c.phone IS NOT NULL')
            ->andWhere(['!=', 'c.phone', ''])
            ->andFilterWhere([
                'like',
                "NULLIF(regexp_replace(c.phone, '\D','','g'), '')",
                preg_replace("/[^0-9]/", '', $phone)
            ]);
    }

    /**
     * Filter by active status
     * @param integer $customer_id
     * @return CompanyCustomerQuery
     */
    public function customer($customer_id)
    {
        return $this->andWhere(['{{%company_customers}}.customer_id' => $customer_id]);
    }

    /**
     * Filter by active status
     * @param boolean $is_active
     * @return CompanyCustomerQuery
     */
    public function active($is_active)
    {
        return $this->andWhere(['{{%company_customers}}.is_active' => $is_active]);
    }

    /**
     * Filter by balance
     * @return CompanyCustomerQuery
     */
    public function hasBalance()
    {
        return $this->andWhere(['<>', '{{%company_customers}}.balance', 0]);
    }
}
