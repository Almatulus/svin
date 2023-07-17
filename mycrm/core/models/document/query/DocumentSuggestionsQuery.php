<?php

namespace core\models\document\query;

/**
 * This is the ActiveQuery class for [[\core\models\document\DocumentSuggestion]].
 *
 * @see \core\models\document\DocumentSuggestion
 */
class DocumentSuggestionsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \core\models\document\DocumentSuggestion[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \core\models\document\DocumentSuggestion|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
