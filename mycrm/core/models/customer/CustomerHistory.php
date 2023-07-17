<?php

namespace core\models\customer;

use core\helpers\HistoryEntityHelper;
use core\models\HistoryEntity;
use Yii;

class CustomerHistory extends HistoryEntity
{
    /**
     * @inheritdoc
     * @return \core\models\query\HistoryEntityQuery the active query used by this AR class.
     */
    public static function find()
    {
        return parent::find()->table(Customer::tableName());
    }

    private $skipSerialization = [
        'access_token', 'created_time', 'key_ios', 'key_android', 'password_hash', 'salt', 'forgot_hash'
    ];

    public function fields()
    {
        $fields = [
            'created_at' => 'created_time',
            'action' => function () {
                return HistoryEntityHelper::getActionLabel($this->event);
            },
            'user' => function () {
                $user = $this->getUser();
                return $user ? $user->getFullName()
                    : Yii::t('app', 'Undefined');
            }
        ];

        if(array_key_exists('new', $this->log)) {
            foreach ($this->log['new'] as $attribute_name => $attribute_value) {

                if(in_array($attribute_name, $this->skipSerialization)) {
                    continue;
                }

                $fields[$attribute_name] = function() use($attribute_value) {
                    return $attribute_value;
                };
            }
        }

        return $fields;
    }
}
