<?php

namespace core\helpers\finance;

use core\models\finance\CompanyCostItem;
use Yii;

class CompanyCostItemHelper
{
    /**
     * @return array
     */
    public static function getInitialItems()
    {
        return [
            [
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'name'           => 'COST ITEM EXPENSE MATERIAL',
                'cost_item_type' => null
            ],
            [
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'name'           => 'COST ITEM EXPENSE PRODUCT',
                'cost_item_type' => null
            ],
            [
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'name'           => 'COST ITEM EXPENSE SALARY',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_SALARY
            ],
            [
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'name'           => 'COST ITEM EXPENSE TAX',
                'cost_item_type' => null
            ],
            [
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'name'           => 'COST ITEM EXPENSE',
                'cost_item_type' => null
            ],
            [
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'name'           => 'COST ITEM EXPENSE REFUND',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_REFUND
            ],
            [
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'name'           => 'COST ITEM EXPENSE DEPOSIT',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_EXPENSE
            ],
            [
                'type'           => CompanyCostItem::TYPE_EXPENSE,
                'name'           => 'COST ITEM EXPENSE CASH TRANSFER',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_EXPENSE_CASH_TRANSFER
            ],
            [
                'type'           => CompanyCostItem::TYPE_INCOME,
                'name'           => 'COST ITEM INCOME CASH TRANSFER',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_INCOME_CASH_TRANSFER
            ],
            [
                'type'           => CompanyCostItem::TYPE_INCOME,
                'name'           => 'COST ITEM INCOME SERVICE',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_SERVICE
            ],
            [
                'type'           => CompanyCostItem::TYPE_INCOME,
                'name'           => 'COST ITEM INCOME DEBT PAYMENT',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEBT_PAYMENT
            ],
            [
                'type'           => CompanyCostItem::TYPE_INCOME,
                'name'           => 'COST ITEM INCOME SUBSCRIPTION',
                'cost_item_type' => null
            ],
            [
                'type'           => CompanyCostItem::TYPE_INCOME,
                'name'           => 'COST ITEM INCOME PRODUCT',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_PRODUCT_SALE
            ],
            [
                'type'           => CompanyCostItem::TYPE_INCOME,
                'name'           => 'COST ITEM INCOME DEPOSIT',
                'cost_item_type' => CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_INCOME
            ],
            [
                'type'           => CompanyCostItem::TYPE_INCOME,
                'name'           => 'COST ITEM INCOME',
                'cost_item_type' => null
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getTypeLabels()
    {
        return [
            CompanyCostItem::TYPE_INCOME  => Yii::t('app', 'CostItem Income'),
            CompanyCostItem::TYPE_EXPENSE => Yii::t('app', 'CostItem Expense'),
        ];
    }
}
