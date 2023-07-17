<?php

namespace core\models\division\query;

use common\components\traits\DivisionTrait;
use core\models\division\DivisionService;
use yii\db\ActiveQuery;

class DivisionServiceQuery extends ActiveQuery
{
    use DivisionTrait;

    /**
     * Filter by company id
     * @param integer|null $company_id
     * @param bool $eagerLoading
     * @return DivisionServiceQuery
     */
    public function company($company_id = null, $eagerLoading = true)
    {
        if ($company_id == null)
            $company_id = \Yii::$app->user->identity->company_id;
        $this->joinWith("divisions", $eagerLoading);
        return $this->andWhere(['{{%divisions}}.company_id' => $company_id]);
    }

    /**
     * Filter by division id
     * @param integer $division_id
     * @param bool $eagerLoading
     * @return DivisionServiceQuery
     */
    public function division($division_id, bool $eagerLoading = true)
    {
        return $this->joinWith('divisions', $eagerLoading)->andFilterWhere(['{{%divisions}}.id' => $division_id]);
    }

    /**
     * Filter by deleted
     * @param boolean $is_deleted
     * @return DivisionServiceQuery
     */
    public function deleted($is_deleted)
    {
        if ($is_deleted)
        {
            return $this->andWhere(['{{%division_services}}.status' => DivisionService::STATUS_DELETED]);
        }
        else
        {
            return $this->andWhere(['{{%division_services}}.status' => DivisionService::STATUS_ENABLED]);
        }
    }

    /**
     * Filter by permitted divisions
     * @param bool $eagerLoading
     * @return DivisionServiceQuery
     */
    public function permitted($eagerLoading = true)
    {
        if (!\Yii::$app->user->isGuest) {
            $divisions = \Yii::$app->user->identity->permittedDivisions;
            if ($divisions) {
                return $this->joinWith('divisions', $eagerLoading)->andWhere([$this->getDivisionAttribute() => $divisions]);
            }
        }
        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId($id)
    {
        return $this->andWhere(['{{%division_services}}.id' => $id]);
    }

    public function getDivisionAttribute()
    {
        return "{{%divisions}}.id";
    }
}
