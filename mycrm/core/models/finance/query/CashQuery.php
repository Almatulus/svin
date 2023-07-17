<?php

namespace core\models\finance\query;
use core\models\finance\CompanyCash;
use yii\db\ActiveQuery;

class CashQuery extends ActiveQuery
{

    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(['{{%company_cashes}}.status' => CompanyCash::STATUS_ENABLED]);
    }

    /**
     * Filter by company id
     * @param integer|null $company_id
     * @return CashQuery
     */
    public function company($company_id = null)
    {
        if ($company_id == null)
            $company_id = \Yii::$app->user->identity->company_id;
        return $this->andWhere(['company_id' => $company_id]);
    }

    /**
     * Filter by division id
     * @param integer|null $division_id
     * @return CashQuery
     */
    public function division($division_id = null) {
        if ($division_id == null) {
            return $this->andWhere(['{{%company_cashes}}.division_id' => \Yii::$app->user->identity->permittedDivisions]);
        }
        return $this->andWhere(['{{%company_cashes}}.division_id' => $division_id]);
    }
}