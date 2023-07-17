<?php

namespace core\models\company\query;

use core\helpers\company\CashbackHelper;

/**
 * This is the ActiveQuery class for [[\core\models\company\Cashback]].
 *
 * @see \core\models\company\Cashback
 */
class CashbackQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\company\Cashback[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\company\Cashback|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param int|null $company_id
     * @param bool $eagerLoading
     * @return self
     */
    public function company(int $company_id = null, bool $eagerLoading = true)
    {
        if (!$company_id) {
            $company_id = \Yii::$app->user->identity->company_id;
        }

        return $this->joinWith('companyCustomer',
            $eagerLoading)->andWhere(['{{%company_customers}}.company_id' => $company_id]);
    }

    /**
     * @return $this
     */
    public function enabled()
    {
        return $this->andWhere(['{{%company_cashbacks}}.status' => CashbackHelper::STATUS_ENABLED]);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andWhere(['{{%company_cashbacks}}.id' => $id]);
    }

    /**
     * @param int $type
     * @return $this
     */
    public function byType(int $type)
    {
        return $this->andWhere(['{{%company_cashbacks}}.type' => $type]);
    }

    /**
     * @return CashbackQuery
     */
    public function in()
    {
        return $this->byType(CashbackHelper::TYPE_IN);
    }

    /**
     * @return CashbackQuery
     */
    public function out()
    {
        return $this->byType(CashbackHelper::TYPE_OUT);
    }
}
