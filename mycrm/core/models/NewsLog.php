<?php

namespace core\models;

use Yii;

/**
 * This is the model class for table "{{%news_logs}}".
 *
 * @property int $id
 * @property string $link
 * @property string $text
 * @property int $status
 */
class NewsLog extends \yii\db\ActiveRecord
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%news_logs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text'], 'string'],
            [['status'], 'default', 'value' => null],
            [['status'], 'integer'],
            [['link'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'link' => Yii::t('app', 'Link'),
            'text' => Yii::t('app', 'Text'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @inheritdoc
     * @return \core\models\query\NewsLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \core\models\query\NewsLogQuery(get_called_class());
    }

    public function fields()
    {
        return ['id', 'text', 'link'];
    }
}
