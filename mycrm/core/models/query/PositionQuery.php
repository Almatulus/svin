<?php

namespace core\models\query;

/**
 * This is the ActiveQuery class for [[\core\models\Position]].
 *
 * @see \core\models\Position
 */
class PositionQuery extends \yii\db\ActiveQuery
{
    public function category($category_id)
    {
        return $this->andWhere(['{{%positions}}.service_category_id' => $category_id]);
    }

    /**
     * @inheritdoc
     * @return \core\models\Position[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\Position|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
