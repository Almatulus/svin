<?php

namespace core\models\warehouse\query;

use core\models\warehouse\Manufacturer;
use yii\db\ActiveQuery;

class ManufacturerQuery extends ActiveQuery
{
    /**
     * Filter by company
     * @param int $company_id
     * @return ManufacturerQuery
     */
    public function company($company_id = null)
    {
        if ( ! $company_id ) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        return $this->andWhere([Manufacturer::tableName() . '.company_id' => $company_id]);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andWhere([Manufacturer::tableName() . '.id' => $id]);
    }
}