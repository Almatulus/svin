<?php

namespace core\repositories;

use core\models\finance\CompanyCostItem;
use core\repositories\exceptions\NotFoundException;

class CompanyCostItemRepository extends BaseRepository
{
    /**
     * @param integer $id
     *
     * @return CompanyCostItem
     * @throws NotFoundException
     */
    public function find($id)
    {
        if (!$model = CompanyCostItem::findOne($id)) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $company_id
     *
     * @return CompanyCostItem
     * @throws NotFoundException
     */
    public function findOrderCostItemByCompany($company_id)
    {
        /* @var CompanyCostItem $model */
        $model = CompanyCostItem::find()
            ->company($company_id)
            ->isService()
            ->orderBy('id')
            ->one();
        if ($model == null) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $company_id
     *
     * @return CompanyCostItem
     * @throws NotFoundException
     */
    public function findOrderProductCostItemByCompany($company_id)
    {
        /* @var CompanyCostItem $model */
        $model = CompanyCostItem::find()
            ->company($company_id)
            ->isProductSale()
            ->orderBy('id')
            ->one();
        if ($model == null) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $company_id
     *
     * @return CompanyCostItem
     * @throws NotFoundException
     */
    public function findDebtPaymentCostItemByCompany($company_id)
    {
        /* @var CompanyCostItem $model */
        $model = CompanyCostItem::find()
            ->company($company_id)
            ->isDebtPayment()
            ->orderBy('id')
            ->one();
        if ($model == null) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param integer $company_id
     *
     * @return CompanyCostItem
     * @throws NotFoundException
     */
    public function findSalaryPaymentCostItemByCompany($company_id)
    {
        /* @var CompanyCostItem $model */
        $model = CompanyCostItem::find()
            ->company($company_id)
            ->isSalary()
            ->orderBy('id')
            ->one();
        if ($model == null) {
            throw new NotFoundException('Model not found.');
        }
        return $model;
    }

    /**
     * @param $id
     * @param $division_id
     *
     * @throws \yii\db\Exception
     */
    public function linkDivision($id, $division_id)
    {
        $command = \Yii::$app->getDb()->createCommand();
        $command->insert("{{%division_cost_items}}", [
            'cost_item_id' => $id,
            'division_id'  => $division_id
        ])->execute();
    }

    /**
     * @param $id
     *
     * @throws \yii\db\Exception
     */
    public function unlinkAllDivisions($id)
    {
        $command = \Yii::$app->getDb()->createCommand();
        $command->delete("{{%division_cost_items}}", [
            'cost_item_id' => $id
        ])->execute();
    }

    /**
     * @param int $company_id
     *
     * @return CompanyCostItem|null
     */
    public function findRefundCostItem(int $company_id)
    {
        return CompanyCostItem::find()->company($company_id)->isRefund()->one();
    }

    /**
     * @param int $company_id
     *
     * @return array|null|CompanyCostItem
     */
    public function findDepositExpense(int $company_id)
    {
        return CompanyCostItem::find()->company($company_id)
            ->andWhere(['cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_EXPENSE])
            ->one();
    }

    /**
     * @param int $company_id
     *
     * @return array|null|CompanyCostItem
     */
    public function findCashTransferIncome(int $company_id)
    {
        return CompanyCostItem::find()->company($company_id)
            ->andWhere(['cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_INCOME_CASH_TRANSFER])
            ->one();
    }

    /**
     * @param int $company_id
     *
     * @return array|null|CompanyCostItem
     */
    public function findDepositIncome(int $company_id)
    {
        return CompanyCostItem::find()->company($company_id)
            ->andWhere(['cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_INCOME])
            ->one();
    }

    /**
     * @param int $company_id
     *
     * @return array|null|CompanyCostItem
     */
    public function findCashTransferExpense(int $company_id)
    {
        return CompanyCostItem::find()->company($company_id)
            ->andWhere(['cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_EXPENSE_CASH_TRANSFER])
            ->one();
    }
}
