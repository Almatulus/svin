<?php

namespace core\models\document\query;

/**
 * This is the ActiveQuery class for [[\core\models\document\DocumentService]].
 *
 * @see \core\models\document\DocumentService
 */
class DocumentServiceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \core\models\document\DocumentService[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\document\DocumentService|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
