<?php

namespace core\models\warehouse\query;

use core\models\warehouse\Delivery;
use yii\db\ActiveQuery;

class DeliveryQuery extends ActiveQuery
{
    /**
     * Filter by company
     * @param int $company_id
     * @return DeliveryQuery
     */
    public function company($company_id = null)
    {
        if ( ! $company_id ) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        return $this->andWhere([Delivery::tableName() . '.company_id' => $company_id]);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andWhere([Delivery::tableName() . '.id' => $id]);
    }

    /**
     * @return $this
     */
    public function enabled()
    {
        return $this->andWhere(['{{%warehouse_delivery}}.is_deleted' => false]);
    }

    /**
     * @return $this
     */
    public function permitted()
    {
        return $this->andWhere(['{{%warehouse_delivery}}.division_id' => \Yii::$app->user->identity->permittedDivisions]);
    }
}