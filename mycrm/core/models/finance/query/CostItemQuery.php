<?php

namespace core\models\finance\query;

use core\models\finance\CompanyCostItem;
use yii\db\ActiveQuery;

class CostItemQuery extends ActiveQuery
{
    /**
     * Filter by company id
     *
     * @param integer|null $company_id
     *
     * @return CostItemQuery
     */
    public function company($company_id = null)
    {
        if ($company_id == null) {
            $company_id = \Yii::$app->user->identity->company_id;
        }

        return $this->andWhere(['{{%company_cost_items}}.company_id' => $company_id]);
    }

    /**
     * @return CostItemQuery
     */
    public function deletable()
    {
        return $this->andWhere(['{{%company_cost_items}}.is_deletable' => true]);
    }

    /**
     * @return CostItemQuery
     */
    public function notDeletable()
    {
        return $this->andWhere(['{{%company_cost_items}}.is_deletable' => false]);
    }

    /**
     * Filter by root cost items
     *
     * @return CostItemQuery
     */
    public function isService()
    {
        return $this->andWhere(['{{%company_cost_items}}.cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_SERVICE]);
    }

    /**
     * Filter by cost item type
     *
     * @return CostItemQuery
     */
    public function isProductSale()
    {
        return $this->andWhere(['{{%company_cost_items}}.cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_PRODUCT_SALE]);
    }

    /**
     * Filter by root cost items
     *
     * @return CostItemQuery
     */
    public function isSalary()
    {
        return $this->andWhere(['{{%company_cost_items}}.cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_SALARY]);
    }

    /**
     * @return $this
     */
    public function isDebtPayment()
    {
        return $this->andWhere(['{{%company_cost_items}}.cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEBT_PAYMENT]);
    }

    /**
     * @return $this
     */
    public function isRefund()
    {
        return $this->andWhere(['{{%company_cost_items}}.cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_REFUND]);
    }

    /**
     * Filter by income type
     *
     * @return CostItemQuery
     */
    public function income()
    {
        return $this->andWhere(['{{%company_cost_items}}.type' => CompanyCostItem::TYPE_INCOME]);
    }

    /**
     * Filter by expense type
     *
     * @return CostItemQuery
     */
    public function expense()
    {
        return $this->andWhere(['{{%company_cost_items}}.type' => CompanyCostItem::TYPE_EXPENSE]);
    }

    /**
     * Filter order payments
     *
     * @return CostItemQuery
     */
    public function orderPayment()
    {
        return $this->andWhere(['{{%company_cost_items}}.cost_item_type' => [
            CompanyCostItem::COST_ITEM_TYPE_REFUND,
            CompanyCostItem::COST_ITEM_TYPE_SERVICE,
            CompanyCostItem::COST_ITEM_TYPE_PRODUCT_SALE,
            CompanyCostItem::COST_ITEM_TYPE_DEBT_PAYMENT,
        ]]);
    }

    /**
     * Filter by divisions
     *
     * @return CostItemQuery
     */
    public function permitted()
    {
        return $this->joinWith('divisions', false)
            ->notDeletable()
            ->orFilterWhere([
                '{{%division_cost_items}}.division_id' => \Yii::$app->user->identity->permittedDivisions
            ]);
    }

    public function category($category_id)
    {
        return $this->andWhere([
            'category_id' => $category_id
        ]);
    }
}
