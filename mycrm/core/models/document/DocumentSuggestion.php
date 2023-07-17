<?php

namespace core\models\document;

use Yii;

/**
 * This is the model class for table "{{%document_suggestions}}".
 *
 * @property int $id
 * @property string $text
 */
class DocumentSuggestion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%document_suggestions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['text'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'text' => Yii::t('app', 'Text'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \core\models\document\query\DocumentSuggestionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\document\query\DocumentSuggestionsQuery(get_called_class());
    }
}
