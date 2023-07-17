<?php

namespace core\models\order\query;

use core\helpers\order\OrderConstants;
use core\models\customer\CompanyCustomer;
use yii\db\ActiveQuery;

class OrderQuery extends ActiveQuery
{
    /**
     * Filter by canceled order status
     * @return OrderQuery
     */
    public function canceled()
    {
        return $this->andWhere(['in', '{{%orders}}.status', [OrderConstants::STATUS_DISABLED, OrderConstants::STATUS_CANCELED]]);
    }

    /**
     * Filter by own company
     * @param bool $eagerLoading
     * @param null $company_id
     * @return OrderQuery
     */
    public function company($eagerLoading = true, $company_id = null)
    {
        if ($company_id == null) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        return $this->joinWith('companyCustomer', $eagerLoading)
            ->andWhere(['{{%company_customers}}.company_id' => $company_id]);
    }

    /**
     * Filter by enabled staff
     * @param CompanyCustomer $companyCustomer
     * @return OrderQuery
     */
    public function companyCustomer(CompanyCustomer $companyCustomer)
    {
        return $this->andWhere(['{{%orders}}.company_customer_id' => $companyCustomer->id]);
    }

    /**
     * Filter by customer
     * @param integer $company_customer_id
     * @return OrderQuery
     */
    public function companyCustomerID($company_customer_id)
    {
        return $this->andWhere(['{{%orders}}.company_customer_id' => $company_customer_id]);
    }

    /**
     * Filter by division
     * @param integer $division_id
     * @return OrderQuery
     */
    public function division($division_id)
    {
        return $this->andFilterWhere(['{{%orders}}.division_id' => $division_id]);
    }

    /**
     * Filter by division service status
     * @param integer $status
     * @return OrderQuery
     */
    public function divisionServiceStatus($status)
    {
        return $this->joinWith('divisionServices')->andWhere(['{{%division_services}}.status' => $status]);
    }

    /**
     * Filters enabled orders having status enabled or finished
     * @return OrderQuery
     */
    public function enabled()
    {
        return $this->andWhere(['in', '{{%orders}}.status', [
                OrderConstants::STATUS_ENABLED,
                OrderConstants::STATUS_FINISHED
            ]
        ]);
    }

    /**
     * Filter by finished order status
     * @return OrderQuery
     */
    public function finished()
    {
        return $this->status(OrderConstants::STATUS_FINISHED);
    }

    /**
     * Orders in the past
     * @return OrderQuery
     */
    public function future()
    {
        return $this->startFrom(new \DateTime());
    }

    /**
     * Sort by datetime
     * @param integer $direction
     * @return OrderQuery
     */
    public function orderDatetime($direction)
    {
        return $this->orderBy(['{{%orders}}.datetime' => $direction]);
    }

    public function datetime(\DateTime $datetime)
    {
        return $this->andFilterWhere(['{{%orders}}.datetime' => $datetime->format('Y-m-d H:i:s')]);
    }

    /**
     * Orders in the past
     * @return OrderQuery
     */
    public function passed()
    {
        return $this->to(new \DateTime());
    }

    /**
     * Filter by staff
     * @param mixed $staff_id
     * @return OrderQuery
     */
    public function staff($staff_id)
    {
        return $this->andFilterWhere(['{{%orders}}.staff_id' => $staff_id]);
    }

    /**
     * Filter orders starting from
     * @param \DateTime $dateTime
     * @param bool $fromStart
     * @return OrderQuery
     */
    public function startFrom(\DateTime $dateTime, bool $fromStart = true)
    {
        $datetime = $fromStart ? $dateTime->format("Y-m-d 00:00:00") : $dateTime->format("Y-m-d H:i:s");
        return $this->andWhere(['>=', '{{%orders}}.datetime', $datetime]);
    }

    /**
     * Filter by order status
     * @param integer $status
     * @return OrderQuery
     */
    public function status($status)
    {
        return $this->andWhere(['{{%orders}}.status' => $status]);
    }

    /**
     * Filter orders starting to
     * @param \DateTime $dateTime
     * @return OrderQuery
     */
    public function to(\DateTime $dateTime)
    {
        return $this->andWhere(['<=', '{{%orders}}.datetime', $dateTime->format("Y-m-d 23:59:59")]);
    }

    /**
     * @return $this
     */
    public function visible()
    {
        return $this->andWhere(['in', '{{%orders}}.status', [
                OrderConstants::STATUS_ENABLED,
                OrderConstants::STATUS_FINISHED,
                OrderConstants::STATUS_CANCELED
            ]
        ]);
    }

    public function waiting()
    {
        return $this->andWhere(['{{%orders}}.status' => OrderConstants::STATUS_WAITING]);
    }

    /**
     * Filter by permitted divisions
     * @return OrderQuery
     */
    public function permitted()
    {
        return $this->division(\Yii::$app->user->identity->permittedDivisions);
    }

    /**
     * Filter by order status
     * @param integer|array $status
     * @return OrderQuery
     */
    public function filterByStatus($status)
    {
        return $this->andFilterWhere(['{{%orders}}.status' => $status]);
    }

    /**
     * @return $this
     */
    public function paid()
    {
        return $this->andWhere(['{{%orders}}.is_paid' => true]);

    }

    /**
     * @return $this
     */
    public function unpaid()
    {
        return $this->andWhere(['{{%orders}}.is_paid' => false]);
    }
}