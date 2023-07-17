<?php

namespace core\forms;

use Yii;
use yii\base\Model;

class HelpForm extends Model
{

    const DATA_DIRECTORY = "data";

    public $query;
    public $email;
    public $attachment;

    private $_filename;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['query', 'email'], 'required'],
            ['email', 'email'],
            [['attachment'], 'file', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'query' => Yii::t('app', 'Query'),
            'email' => Yii::t('app', 'Email'),
            'attachment' => Yii::t('app', 'Attachment'),
        ];
    }

    public function saveAttachment()
    {
        $directory       = Yii::$app->basePath . DIRECTORY_SEPARATOR . self::DATA_DIRECTORY;
        $this->_filename = $directory . DIRECTORY_SEPARATOR . $this->attachment->name;
        $this->attachment->saveAs($this->_filename);
    }

    public function deleteAttachment()
    {
        unlink($this->_filename);
    }

    public function getFilename()
    {
        return $this->_filename;
    }
}