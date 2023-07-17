<?php

namespace core\models\finance\query;

use core\models\finance\CompanyCostItemCategory;
use yii\db\ActiveQuery;

class CostItemCategoryQuery extends ActiveQuery
{
    /**
     * Filter by company id
     *
     * @param integer|null $company_id
     *
     * @return CostItemCategoryQuery
     */
    public function company($company_id = null)
    {
        if ($company_id == null) {
            $company_id = \Yii::$app->user->identity->company_id;
        }

        return $this->andWhere([
            CompanyCostItemCategory::tableName() . '.company_id' => $company_id
        ]);
    }
}