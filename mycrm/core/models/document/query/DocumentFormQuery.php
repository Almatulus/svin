<?php

namespace core\models\document\query;

/**
 * This is the ActiveQuery class for [[\core\models\document\DocumentForm]].
 *
 * @see \core\models\document\DocumentForm
 */
class DocumentFormQuery extends \yii\db\ActiveQuery
{
    /**
     * @return $this
     */
    public function enabled()
    {
        return $this->andWhere(['{{%document_forms}}.enabled' => true]);
    }

    /**
     * @inheritdoc
     * @return \core\models\document\DocumentForm[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \core\models\document\DocumentForm|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
