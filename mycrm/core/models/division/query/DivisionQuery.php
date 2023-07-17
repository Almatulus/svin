<?php

namespace core\models\division\query;

use common\components\traits\DivisionTrait;
use core\models\division\Division;
use yii\db\ActiveQuery;

class DivisionQuery extends ActiveQuery
{
    use DivisionTrait;

    /**
     * @param null $company_id
     *
     * @return $this
     */
    public function company($company_id = null)
    {
        if (!$company_id) {
            $company_id = \Yii::$app->user->identity->company_id;
        }

        return $this->andWhere(['{{%divisions}}.company_id' => $company_id]);
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function id($id)
    {
        return $this->andFilterWhere(['{{%divisions}}.id' => $id]);
    }

    /**
     * @return $this
     */
    public function enabled()
    {
        return $this->andWhere(['{{%divisions}}.status' => Division::STATUS_ENABLED]);
    }

    /**
     * Filter by published
     *
     * @param boolean $published
     *
     * @return DivisionQuery
     */
    public function publish($published)
    {
        return $this->joinWith('company')
            ->andWhere(['{{%companies}}.publish' => $published]);
    }

    /**
     * Filter by category
     *
     * @param array|int $category
     *
     * @return DivisionQuery
     */
    public function category($category)
    {
        return $this->andWhere(['{{%divisions}}.category_id' => $category]);
    }

    /**
     * @return string
     */
    public function getDivisionAttribute()
    {
        return "{{%divisions}}.id";
    }

    /**
     * @param bool $eagerLoading
     * @return $this
     */
    public function active(bool $eagerLoading = true)
    {
        $week_ago = date('Y-m-d 00:00:00', time() - 7 * 24 * 60 * 60);
        return $this->joinWith('orders', $eagerLoading)
            ->andWhere([
                '>=',
                '{{%orders}}.created_time',
                $week_ago
            ]);
    }
}