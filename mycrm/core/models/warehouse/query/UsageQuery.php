<?php

namespace core\models\warehouse\query;

use core\models\warehouse\Usage;
use yii\db\ActiveQuery;

class UsageQuery extends ActiveQuery
{
    /**
     * Filter by company
     * @param bool $eagerLoading
     * @return UsageQuery
     */
    public function company($eagerLoading = true)
    {
        $company_id = \Yii::$app->user->identity->company_id;
        return $this->andWhere(['{{%warehouse_usage}}.company_id' => $company_id]);
    }

    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(['{{%warehouse_usage}}.status' => [Usage::STATUS_ACTIVE, Usage::STATUS_CANCELED]]);
    }

    /**
     * @return $this
     */
    public function enabled()
    {
        return $this->andWhere(['{{%warehouse_usage}}.status' => Usage::STATUS_ACTIVE]);
    }

    public function order($order_id = null) {
        if ($order_id) {
            return $this
                ->join('INNER JOIN', '{{%order_usage}}', '{{%order_usage}}.usage_id = {{%warehouse_usage}}.id')
                ->andWhere(["{{%order_usage}}.order_id" => $order_id]);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function permitted()
    {
        return $this->andFilterWhere(['{{%warehouse_usage}}.division_id' => \Yii::$app->user->identity->permittedDivisions]);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andWhere([Usage::tableName() . '.id' => $id]);
    }
}