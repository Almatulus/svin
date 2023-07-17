<?php

namespace core\models\warehouse\query;
use core\models\warehouse\Stocktake;
use Yii;

/**
 * This is the ActiveQuery class for [[\core\models\warehouse\Stocktake]].
 *
 * @see \core\models\warehouse\Stocktake
 */
class StocktakeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\warehouse\Stocktake[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\warehouse\Stocktake|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Filter by company
     * @param null $company_id
     * @return StocktakeQuery
     */
    public function company($company_id = null)
    {
        if (!$company_id) { $company_id = Yii::$app->user->identity->company_id; }
        return $this->andWhere(['{{%warehouse_stocktake}}.company_id' => $company_id]);
    }

    /**
     * @return $this
     */
    public function permitted()
    {
        return $this->andFilterWhere(['{{%warehouse_stocktake}}.division_id' => Yii::$app->user->identity->permittedDivisions]);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function byId(int $id)
    {
        return $this->andWhere([Stocktake::tableName() . '.id' => $id]);
    }
}
