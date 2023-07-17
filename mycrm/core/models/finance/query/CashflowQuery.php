<?php

namespace core\models\finance\query;

use core\models\finance\CompanyCash;
use core\models\finance\CompanyCashflow;
use core\models\finance\CompanyCostItem;
use yii\db\ActiveQuery;

class CashflowQuery extends ActiveQuery
{
    /**
     * Return only active elements
     *
     * @return CashflowQuery
     */
    public function active()
    {
        return $this->andWhere([
            '{{%company_cashflows}}.status'     => CompanyCashflow::STATUS_ACTIVE,
            '{{%company_cashflows}}.is_deleted' => false
        ]);
    }

    /**
     * @param integer $cash_id
     *
     * @return CashflowQuery
     */
    public function cash($cash_id)
    {
        return $this->andFilterWhere(['{{%company_cashflows}}.cash_id' => $cash_id])
            ->joinWith('cash')
            //->andWhere(['{{%company_cashes}}.status' => CompanyCash::STATUS_ENABLED])
        ;
    }

    /**
     * @param $company_id
     *
     * @return CashflowQuery
     */
    public function company(int $company_id)
    {
        return $this->andWhere(['{{%company_cashflows}}.company_id' => $company_id]);
    }

    /**
     * @param integer $cost_item_id
     *
     * @return CashflowQuery
     */
    public function costItem($cost_item_id)
    {
        return $this->andFilterWhere(['{{%company_cashflows}}.cost_item_id' => $cost_item_id]);
    }

    /**
     * @param null $division_id
     * @return CashflowQuery
     */
    public function division($division_id = null)
    {
        return $this->andFilterWhere(['{{%company_cashflows}}.division_id' => $division_id]);
    }

    /**
     * @param bool $eagerLoading
     *
     * @return CashflowQuery
     */
    public function expense($eagerLoading = true)
    {
        return $this->joinWith('costItem ci', $eagerLoading)
            ->andWhere(['ci.type' => CompanyCostItem::TYPE_EXPENSE]);
    }

    /**
     * @param bool $eagerLoading
     *
     * @return CashflowQuery
     */
    public function income($eagerLoading = true)
    {
        return $this->joinWith('costItem ci', $eagerLoading)
            ->andWhere(['ci.type' => CompanyCostItem::TYPE_INCOME]);
    }

    /**
     * Filter by permitted divisions
     * @return CashflowQuery
     */
    public function permittedDivisions()
    {
        return $this->division(\Yii::$app->user->identity->permittedDivisions);
    }

    /**
     * ToDo should operator be gt or g? Consider. Temporary solution is to add additional flag (inclusive)
     * @param $start
     * @param $end
     * @param bool $inclusive
     * @return CashflowQuery
     */
    public function range($start, $end, $inclusive = true)
    {
        $this->andWhere(':startDate <= date', [':startDate' => $start]);
        if ($inclusive) {
            return $this->andWhere('date <= :finishDate', [':finishDate' => $end]);
        }
        return $this->andWhere('date < :finishDate', [':finishDate' => $end]);
    }

    /**
     * @param null $staff_id
     * @return $this
     */
    public function staff($staff_id)
    {
        return $this->andFilterWhere(['{{%company_cashflows}}.staff_id' => $staff_id]);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function until($date) {
        return $this->andWhere(['<', 'date', $date]);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function from($date) {
        return $this->andWhere(['>', 'date', $date]);
    }

    public function forReport()
    {
        return $this->joinWith('costItem')
            ->andWhere([
                'NOT',
                ['{{%company_cost_items}}.cost_item_type' => [
                    CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_EXPENSE,
                    CompanyCostItem::COST_ITEM_TYPE_DEPOSIT_INCOME,
                ]]
            ]);
    }

    /**
     * @return CashflowQuery
     */
    public function week() {
        $from = date("Y-m-d H:i:s", strtotime('-7 day'));

        return $this
            ->andWhere(['>', 'date', $from]);
    }

    public function payment($payment_types) 
    {
        return $this->joinWith('payments')
            ->andFilterWhere(['{{%company_cashflow_payments}}.payment_id' => $payment_types]);
    }
}
