<?php

namespace core\forms\webcall;

use core\models\webcall\WebCall;
use Yii;
use yii\base\Model;

/**
 * WebCallForm is the model behind the webcall api calls.
 *
 * @property string  $api_key
 * @property string  $username
 * @property string  $domain
 */
class WebCallUpdateForm extends Model
{
    public $api_key;
    public $username;
    public $domain;

    public function __construct(WebCall $model, array $config = [])
    {
        $this->attributes = $model->attributes;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['api_key', 'domain', 'username'], 'required'],
            [['api_key', 'domain', 'username'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'api_key'  => Yii::t('app', 'Api Key'),
            'username' => Yii::t('app', 'Api username'),
            'domain'   => Yii::t('app', 'Domain'),
        ];
    }
}
