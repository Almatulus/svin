<?php

namespace core\models\company\query;

/**
 * This is the ActiveQuery class for [[\core\models\company\Tariff]].
 *
 * @see \core\models\company\Tariff
 */
class TariffQuery extends \yii\db\ActiveQuery
{
    /**
     * @return $this
     */
    public function enabled()
    {
        return $this->andWhere(['is_deleted' => false]);
    }

    /**
     * @inheritdoc
     * @return \core\models\company\Tariff[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\company\Tariff|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
