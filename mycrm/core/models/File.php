<?php

namespace core\models;

use function foo\func;
use Yii;

/**
 * This is the model class for table "{{%s3_files}}".
 *
 * @property integer $id
 * @property string  $path
 * @property string  $name
 * @property string  $created_at
 */
class File extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%s3_files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['path'], 'required'],
            [['path', 'name'], 'string', 'max' => 255],
        ];
    }

    public static function add(
        string $path,
        string $name = null
    ) {
        $model             = new File();
        $model->path       = $path;
        $model->name       = $name;
        $model->created_at = date('Y-m-d H:i:d');

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'   => Yii::t('app', 'ID'),
            'path' => Yii::t('app', 'Path'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'path',
            'name',
            'extension' => function () {
                return pathinfo($this->path)['extension'];
            },
            'created_at',
        ];
    }
}
